"""Faux module RPi.GPIO pour l'environnement Docker local.

- GPIO.input(pin) renvoie le contenu de docker/gpio/<pin> (0 ou 1), 1 par défaut
  -> pour simuler un capteur : echo 0 > docker/gpio/24
- GPIO.output() et les autres appels sont tracés dans var/log/gpio-fake.log
- outputs : dernier niveau écrit par broche (utilisé par les tests Python)
"""
import datetime
import os

BCM = 11
BOARD = 10
IN = 1
OUT = 0
HIGH = 1
LOW = 0
PUD_UP = 22
PUD_DOWN = 21
PUD_OFF = 20
RISING = 31
FALLING = 32
BOTH = 33
VERSION = "fake-docker"

_PIN_DIR = os.environ.get("FAKE_GPIO_DIR", "/opt/gpio")
_LOG = os.environ.get("FAKE_GPIO_LOG", "/var/www/html/aqua/var/log/gpio-fake.log")
outputs = {}


def _log(msg):
    try:
        with open(_LOG, "a") as f:
            f.write("%s [gpio-fake] %s\n" % (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), msg))
    except OSError:
        pass


def setmode(mode):
    pass


def setwarnings(flag):
    pass


def setup(channel, direction, pull_up_down=PUD_OFF, initial=None):
    if direction == OUT and initial is not None:
        output(channel, initial)


def input(channel):
    try:
        with open(os.path.join(_PIN_DIR, str(channel))) as f:
            return int(f.read().strip() or 1)
    except (OSError, ValueError):
        return HIGH


def output(channel, value):
    outputs[channel] = HIGH if value else LOW
    _log("output pin=%s value=%s" % (channel, "HIGH" if value else "LOW"))


def add_event_detect(channel, edge, callback=None, bouncetime=None):
    _log("add_event_detect pin=%s" % channel)


def remove_event_detect(channel):
    pass


def cleanup(channel=None):
    pass
