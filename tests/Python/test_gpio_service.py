"""Tests du service GPIO (faux RPi.GPIO de docker/python) : make test-python."""
import json
import os
import socket
import sys
import tempfile
import threading
import unittest

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
sys.path.insert(0, os.path.join(ROOT, 'docker', 'python'))
sys.path.insert(0, os.path.join(ROOT, 'scripts'))

import RPi.GPIO as GPIO  # noqa: E402  (faux module)
import gpio_service  # noqa: E402

CONFIG = gpio_service.load_config(gpio_service.DEFAULT_CONFIG)


class FakeClock:
    def __init__(self):
        self.now = 1000.0

    def __call__(self):
        return self.now


class BoardTest(unittest.TestCase):
    def setUp(self):
        self.pins = tempfile.TemporaryDirectory()
        GPIO._PIN_DIR = self.pins.name
        GPIO._LOG = os.devnull
        GPIO.outputs.clear()
        self.clock = FakeClock()
        self.board = gpio_service.Board(CONFIG, clock=self.clock)

    def tearDown(self):
        self.pins.cleanup()

    def pin(self, relay):
        return CONFIG['relays'][relay]['pin']

    def write_input(self, name, value):
        with open(os.path.join(self.pins.name, str(CONFIG['inputs'][name])), 'w') as f:
            f.write(str(value))

    def test_all_relays_off_at_startup(self):
        self.assertEqual({'ecumeur': False, 'pompe_osmolateur': False, 'refuge_eclairage': False}, self.board.status())
        self.assertEqual(GPIO.HIGH, GPIO.outputs[self.pin('pompe_osmolateur')], 'active à l\'état bas : coupée = HIGH')
        self.assertEqual(GPIO.LOW, GPIO.outputs[self.pin('ecumeur')], 'écumeur actif à l\'état haut : coupé = LOW')

    def test_relay_levels_follow_active_low(self):
        self.board.set('pompe_osmolateur', True)
        self.board.set('ecumeur', True)
        self.assertEqual(GPIO.LOW, GPIO.outputs[self.pin('pompe_osmolateur')])
        self.assertEqual(GPIO.HIGH, GPIO.outputs[self.pin('ecumeur')])
        self.board.set('pompe_osmolateur', False)
        self.assertEqual(GPIO.HIGH, GPIO.outputs[self.pin('pompe_osmolateur')])

    def test_read_inputs(self):
        self.write_input('osmolateur_niveau_ok', 0)
        values = self.board.read(['osmolateur_niveau_ok', 'osmolateur_trop_haut'])
        self.assertEqual({'osmolateur_niveau_ok': 0, 'osmolateur_trop_haut': 1}, values)

    def test_unknown_names_are_rejected(self):
        with self.assertRaises(ValueError):
            self.board.read(['inconnue'])
        with self.assertRaises(ValueError):
            self.board.read([])
        with self.assertRaises(ValueError):
            self.board.set('inconnu', True)

    def test_watchdog_cuts_pump_without_refresh(self):
        self.board.set('pompe_osmolateur', True)
        self.board.set('refuge_eclairage', True)  # pas de watchdog : reste allumé
        self.clock.now += 10
        self.board.set('pompe_osmolateur', True)  # commande renouvelée
        self.clock.now += 10
        self.assertEqual([], self.board.check_watchdogs())
        self.clock.now += 6
        self.assertEqual(['pompe_osmolateur'], self.board.check_watchdogs())
        self.assertFalse(self.board.status()['pompe_osmolateur'])
        self.assertTrue(self.board.status()['refuge_eclairage'])
        self.assertEqual(GPIO.HIGH, GPIO.outputs[self.pin('pompe_osmolateur')])

    def test_all_off(self):
        self.board.set('refuge_eclairage', True)
        self.board.all_off()
        self.assertFalse(any(self.board.status().values()))

    def test_handle_requests(self):
        self.assertEqual({'ok': True}, gpio_service.handle(self.board, {'op': 'ping'}))
        self.assertEqual({'ok': True, 'on': True}, gpio_service.handle(self.board, {'op': 'set', 'relay': 'ecumeur', 'on': True}))
        self.assertTrue(gpio_service.handle(self.board, {'op': 'status'})['relays']['ecumeur'])
        self.assertFalse(gpio_service.handle(self.board, {'op': 'read', 'inputs': ['x']})['ok'])
        self.assertFalse(gpio_service.handle(self.board, {'op': 'reboot'})['ok'])


class ServerTest(unittest.TestCase):
    def test_tcp_round_trip(self):
        GPIO._LOG = os.devnull
        server = gpio_service.Server(('127.0.0.1', 0), gpio_service.Board(CONFIG))
        threading.Thread(target=server.serve_forever, daemon=True).start()
        try:
            with socket.create_connection(server.server_address, timeout=2) as conn:
                f = conn.makefile('rwb')
                for request, key in (({'op': 'set', 'relay': 'refuge_eclairage', 'on': True}, 'on'), ({'op': 'status'}, 'relays')):
                    f.write((json.dumps(request) + '\n').encode())
                    f.flush()
                    self.assertIn(key, json.loads(f.readline()))
                f.write(b'pas du json\n')
                f.flush()
                self.assertEqual({'ok': False, 'error': 'JSON invalide'}, json.loads(f.readline()))
        finally:
            server.shutdown()
            server.server_close()

    def test_self_ping(self):
        GPIO._LOG = os.devnull
        server = gpio_service.Server(('127.0.0.1', 0), gpio_service.Board(CONFIG))
        threading.Thread(target=server.serve_forever, daemon=True).start()
        port = server.server_address[1]
        try:
            self.assertTrue(gpio_service.self_ping(port))
        finally:
            server.shutdown()
            server.server_close()
        self.assertFalse(gpio_service.self_ping(port, timeout=1), 'serveur arrêté : ping en échec')


if __name__ == '__main__':
    unittest.main()
