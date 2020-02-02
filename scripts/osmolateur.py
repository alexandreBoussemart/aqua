#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import sys
from time import sleep
import functions

import RPi.GPIO as GPIO

relais = 21
to_high = 23
level_ok = 24
pump_on = 25
to_low = 27

path = 'osmolateur'

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(to_high, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(to_low, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(pump_on, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(level_ok, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO led to high
    TO_HIGH_STATE = str(GPIO.input(to_high))

    # lecture GPIO led to low
    TO_LOW_STATE = str(GPIO.input(to_low))

    # lecture GPIO led pump on
    PUMP_ON_STATE = str(GPIO.input(pump_on))

    # lecture GPIO led level ok
    LEVEL_OK_STATE = str(GPIO.input(level_ok))

    # on fait la chaine de caract�re du statut
    state = TO_HIGH_STATE + LEVEL_OK_STATE + PUMP_ON_STATE + TO_LOW_STATE

    # on fait une deuxi�me lecture pour �tre s�r
    GPIO.setup(to_high, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(to_low, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(pump_on, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(level_ok, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO led to high
    TO_HIGH_STATE = str(GPIO.input(to_high))

    # lecture GPIO led to low
    TO_LOW_STATE = str(GPIO.input(to_low))

    # lecture GPIO led pump on
    PUMP_ON_STATE = str(GPIO.input(pump_on))

    # lecture GPIO led level ok
    LEVEL_OK_STATE = str(GPIO.input(level_ok))

    # on fait la chaine de caract�re du statut
    state2 = TO_HIGH_STATE + LEVEL_OK_STATE + PUMP_ON_STATE + TO_LOW_STATE

    # si les deux mesures sont identiques
    if state == state2:
        # niveau d'eau TO HIGHT
        if state == '0111':
            functions.setosmolateur("to_high")
            message = "Osmolateur - ERREUR - niveau d'eau TO HIGHT"
            functions.setcompletestate(path, 'state_1', 1, message, 0, 0)

        # niveau d'eau OK
        elif state_current == '1011':
            functions.setosmolateur("ok")
            message = "Osmolateur - niveau d'eau OK"
            functions.setcompletestate(path, 'state_2', 0, message, 0, 0)

        # remplissage en cours
        elif state_current == '1101':
            functions.setosmolateur("pump_on")
            message = "Osmolateur - remplissage en cours"
            functions.setcompletestate(path, 'state_3', 0, message, 0, 0)

        # niveau d'eau TO LOW
        elif state_current == '1110':
            functions.setosmolateur("to_low")
            message = "Osmolateur - ERREUR - niveau d'eau TO LOW"
            functions.setcompletestate(path, 'state_4', 1, message, 1, 0)

        # osmolateur off
        elif state_current == '1111':
            functions.setosmolateur("off")
            message = "Osmolateur - ERREUR - off"
            functions.setcompletestate(path, 'state_5', 1, message, 1, 0)

    functions.setcontrole('controle_osmolateur')
    sys.exit()

except Exception as e:
    message = "Osmolateur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    print(message)
    functions.setcompletestate(path, 'state_6', 1, message, 1, 0)
    functions.mail(message, body)
    functions.offled(relais)

    raise