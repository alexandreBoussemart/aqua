#!/usr/bin/env python3
"""Service GPIO permanent de l'aquarium.

Seul processus à toucher aux GPIO : il garde les broches réservées tant qu'il tourne (les relais
conservent donc leur état), lit les capteurs de niveau et pilote les relais à la demande de l'appli PHP.
Aucune logique métier, aucune base de données : PHP interprète les niveaux (src/Command/Cron/*).

Protocole : TCP, une requête JSON par ligne, une réponse JSON par ligne.
  {"op": "ping"}                                   -> {"ok": true}
  {"op": "read", "inputs": ["osmolateur_niveau_ok"]} -> {"ok": true, "values": {"osmolateur_niveau_ok": 1}}
  {"op": "set", "relay": "pompe_osmolateur", "on": true} -> {"ok": true, "on": true}
  {"op": "status"}                                 -> {"ok": true, "relays": {"ecumeur": false, ...}}
  erreur                                           -> {"ok": false, "error": "..."}

Sécurité :
  - au démarrage, tous les relais sont coupés ; à l'arrêt (SIGTERM), aussi ;
  - « watchdog » (secondes, config/gpio.json) : un relais allumé sans nouvelle commande « on » pendant
    ce délai est coupé (pompe de l'osmolateur : si le cron PHP plante en plein remplissage) ;
  - auto-contrôle : le service s'interroge lui-même toutes les 30 s ; après 3 échecs consécutifs (serveur
    figé), il coupe les relais et s'arrête : Docker le relance.
"""
import argparse
import json
import logging
import os
import signal
import socketserver
import sys
import threading
import time

import RPi.GPIO as GPIO

BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..'))
DEFAULT_CONFIG = os.path.join(BASE_DIR, 'config', 'gpio.json')

log = logging.getLogger('gpio')


class Board:
    """Accès aux GPIO (thread-safe) : entrées pull-up, relais avec logique active à l'état bas ou haut."""

    def __init__(self, config, clock=time.monotonic):
        self.inputs = {name: int(pin) for name, pin in config['inputs'].items()}
        self.relays = {name: dict(spec) for name, spec in config['relays'].items()}
        self.clock = clock
        self.lock = threading.Lock()
        self.state = {}      # relais -> allumé ?
        self.refreshed = {}  # relais -> dernière commande « on »

        GPIO.setwarnings(False)
        GPIO.setmode(GPIO.BCM)
        for pin in self.inputs.values():
            GPIO.setup(pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
        for name, spec in self.relays.items():
            GPIO.setup(spec['pin'], GPIO.OUT, initial=self._level(spec, False))
            self.state[name] = False

    @staticmethod
    def _level(spec, on):
        active = GPIO.LOW if spec.get('active_low', True) else GPIO.HIGH
        inactive = GPIO.HIGH if active == GPIO.LOW else GPIO.LOW
        return active if on else inactive

    def read(self, names):
        unknown = [n for n in names if n not in self.inputs]
        if unknown or not names:
            raise ValueError('entrée inconnue : %s' % ', '.join(unknown or ['(aucune)']))
        with self.lock:
            return {name: int(GPIO.input(self.inputs[name])) for name in names}

    def set(self, name, on):
        if name not in self.relays:
            raise ValueError('relais inconnu : %s' % name)
        with self.lock:
            self._apply(name, bool(on))
            if on:
                self.refreshed[name] = self.clock()
        return self.state[name]

    def _apply(self, name, on):
        GPIO.output(self.relays[name]['pin'], self._level(self.relays[name], on))
        if self.state.get(name) != on:
            log.info('relais %s : %s', name, 'ON' if on else 'OFF')
        self.state[name] = on

    def status(self):
        with self.lock:
            return dict(self.state)

    def check_watchdogs(self):
        """Coupe les relais allumés dont la commande « on » n'a pas été renouvelée à temps. @return relais coupés"""
        cut = []
        with self.lock:
            now = self.clock()
            for name, spec in self.relays.items():
                delay = spec.get('watchdog')
                if delay and self.state.get(name) and now - self.refreshed.get(name, 0) > delay:
                    self._apply(name, False)
                    cut.append(name)
        for name in cut:
            log.warning('relais %s coupé par sécurité : aucune commande depuis plus de %ss', name, self.relays[name]['watchdog'])
        return cut

    def all_off(self):
        with self.lock:
            for name in self.relays:
                self._apply(name, False)


def handle(board, request):
    """Traite une requête décodée. @return dict réponse"""
    try:
        op = request.get('op')
        if op == 'ping':
            return {'ok': True}
        if op == 'read':
            return {'ok': True, 'values': board.read(list(request.get('inputs') or []))}
        if op == 'set':
            return {'ok': True, 'on': board.set(str(request.get('relay')), bool(request.get('on')))}
        if op == 'status':
            return {'ok': True, 'relays': board.status()}
        return {'ok': False, 'error': 'opération inconnue : %s' % op}
    except (ValueError, TypeError, AttributeError) as e:
        return {'ok': False, 'error': str(e)}
    except Exception as e:  # erreur matérielle : renvoyée au client, le service continue
        log.exception('erreur GPIO')
        return {'ok': False, 'error': 'erreur GPIO : %s' % e}


class Handler(socketserver.StreamRequestHandler):
    timeout = 30

    def handle(self):
        for line in self.rfile:
            try:
                request = json.loads(line.decode('utf-8'))
                response = handle(self.server.board, request if isinstance(request, dict) else {})
            except ValueError:
                response = {'ok': False, 'error': 'JSON invalide'}
            self.wfile.write((json.dumps(response) + '\n').encode('utf-8'))


class Server(socketserver.ThreadingTCPServer):
    daemon_threads = True
    allow_reuse_address = True

    def __init__(self, address, board):
        super().__init__(address, Handler)
        self.board = board


def self_ping(port, timeout=5):
    """True si le serveur répond à un ping sur 127.0.0.1:port."""
    import socket
    try:
        with socket.create_connection(('127.0.0.1', port), timeout=timeout) as conn:
            conn.settimeout(timeout)
            conn.sendall(b'{"op": "ping"}\n')
            return json.loads(conn.makefile('rb').readline().decode('utf-8')).get('ok') is True
    except (OSError, ValueError):
        return False


def load_config(path):
    with open(path) as f:
        return json.load(f)


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__.split('\n')[0])
    parser.add_argument('--host', default=os.environ.get('GPIO_SERVICE_HOST', '0.0.0.0'))
    parser.add_argument('--port', type=int, default=int(os.environ.get('GPIO_SERVICE_PORT', '7070')))
    parser.add_argument('--config', default=DEFAULT_CONFIG)
    args = parser.parse_args(argv)

    logging.basicConfig(level=logging.INFO, format='%(asctime)s [gpio] %(levelname)s %(message)s', stream=sys.stdout)
    board = Board(load_config(args.config))
    server = Server((args.host, args.port), board)

    stop = threading.Event()

    def watchdog():
        failures, tick = 0, 0
        while not stop.wait(1):
            board.check_watchdogs()
            tick += 1
            if tick % 30 == 0:
                failures = 0 if self_ping(args.port) else failures + 1
                if failures >= 3:
                    log.error('le service ne répond plus à ses propres requêtes : arrêt (Docker le relance)')
                    board.all_off()
                    os._exit(1)

    def shutdown(signum, frame):
        log.info('arrêt demandé (signal %s) : relais coupés', signum)
        stop.set()
        threading.Thread(target=server.shutdown, daemon=True).start()

    signal.signal(signal.SIGTERM, shutdown)
    signal.signal(signal.SIGINT, shutdown)
    threading.Thread(target=watchdog, daemon=True).start()

    log.info('service GPIO à l\'écoute sur %s:%s (%d entrées, %d relais, tous coupés)',
             args.host, args.port, len(board.inputs), len(board.relays))
    try:
        server.serve_forever()
    finally:
        board.all_off()
        server.server_close()


if __name__ == '__main__':
    main()
