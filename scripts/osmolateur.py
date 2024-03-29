#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import time, sys
from time import sleep
import functions

import RPi.GPIO as GPIO

relay = 21
to_high = 23
level_ok = 24

path = 'osmolateur'

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(to_high, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(level_ok, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO led to high
    TO_HIGH_STATE = str(GPIO.input(to_high))

    # lecture GPIO led level ok
    LEVEL_OK_STATE = str(GPIO.input(level_ok))

    # on fait la chaine de caract�re du statut
    state = TO_HIGH_STATE + LEVEL_OK_STATE

    time.sleep(0.5)

    # on fait une deuxi�me lecture pour �tre s�r
    GPIO.setup(to_high, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(level_ok, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO led to high
    TO_HIGH_STATE = str(GPIO.input(to_high))

    # lecture GPIO led level ok
    LEVEL_OK_STATE = str(GPIO.input(level_ok))

    # on fait la chaine de caract�re du statut
    state2 = TO_HIGH_STATE + LEVEL_OK_STATE

    # si les deux mesures sont identiques
    if state == state2:

        # si sur off
        status = functions.getstatus('on_off_osmolateur')
        if status == 0 or status == "0":
            message = "Osmolateur - off"
            functions.setcompletestate(path, 'state_5', 1, message, 1, 0)

        # niveau d'eau TO HIGHT
        elif TO_HIGH_STATE == '1':
            message = "Osmolateur - ERREUR - niveau eau TO HIGHT"
            functions.setcompletestate(path, 'state_1', 1, message, 0, 0)

        # niveau d'eau OK
        elif LEVEL_OK_STATE == '1':
            message = "Osmolateur - niveau eau OK"

            if functions.instatetrois() is True:
                temps = functions.getconfig('temps_mini_pompe_osmolateur')
                sleep(int(temps))

            functions.setcompletestate(path, 'state_2', 0, message, 0, 0)

        # remplissage en cours
        elif LEVEL_OK_STATE == '0':
            message = "Osmolateur - remplissage en cours"
            if functions.notinstatehuit() is True:
                functions.setcompletestate(path, 'state_3', 0, message, 0, 0)

        # osmolateur off
        else:
            message = "Osmolateur - off"
            functions.setcompletestate(path, 'state_5', 1, message, 1, 0)

    functions.setcontrole('controle_osmolateur')
    sys.exit()

except Exception as e:
    message = "Osmolateur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + " - " + str(e) + "</p>"
    functions.setcompletestate(path, 'state_6', 1, message + " - " + str(e), 1, 0)
    functions.mail(message, body)
    functions.off_relay(relay)

    raise
