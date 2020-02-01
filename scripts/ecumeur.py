#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import sys
from time import sleep
import functions

import RPi.GPIO as GPIO

port = 18
path = 'ecumeur'

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(port, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    state = '2'

    # lecture GPIO led to high
    state_current = str(GPIO.input(port))

    # niveau écumeur eau trop eau
    if state_current == '0':
        message = "Ecumeur - ERREUR - niveau godet trop haut"
        functions.setcompletestate(path, 'state_1', 1, message, 0, 0)

    # niveau eau ok
    elif state_current == '1':
        message = "Ecumeur - niveau godet OK"
        functions.setcompletestate(path, 'state_2', 0, message, 0, 0)

    functions.setcontrole('controle_ecumeur')
    sys.exit()

except Exception as e:
    message = "Ecumeur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    setcompletestate(path, 'state_3', 1, message + ' - ' + str(e), 0, 0)
    functions.mail(message, body)
    functions.setlog(message + ' - ' + str(e))

    raise
