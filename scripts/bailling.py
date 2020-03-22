#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import sys
from time import sleep
import functions

import RPi.GPIO as GPIO

bailling_1 = 5
bailling_2 = 22
bailling_3 = 13
bailling_1_led = 12
bailling_2_led = 19
bailling_3_led = 26
path = 'bailling'
state = '222'

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(bailling_1, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(bailling_2, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(bailling_3, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO bailling_1
    BAILLING_1_STATE = str(GPIO.input(bailling_1))

    # lecture GPIO bailling_2
    BAILLING_2_STATE = str(GPIO.input(bailling_2))

    # lecture GPIO bailling_3
    BAILLING_3_STATE = str(GPIO.input(bailling_3))

    # on fait la chaine de caractère du statut
    state_current = BAILLING_1_STATE + BAILLING_2_STATE + BAILLING_3_STATE

    sleep(20)

    GPIO.setup(bailling_1, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(bailling_2, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(bailling_3, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # lecture GPIO bailling_1
    BAILLING_1_STATE = str(GPIO.input(bailling_1))

    # lecture GPIO bailling_2
    BAILLING_2_STATE = str(GPIO.input(bailling_2))

    # lecture GPIO bailling_3
    BAILLING_3_STATE = str(GPIO.input(bailling_3))

    # on fait la chaine de caractère du statut
    state_current2 = BAILLING_1_STATE + BAILLING_2_STATE + BAILLING_3_STATE

    if state_current == state_current2:

        # niveau bailling 1 bas
        if state_current == '011':
            message = "Bailling - ERREUR - niveau 1 bas"
            functions.offled(bailling_1_led)
            functions.onled(bailling_2_led)
            functions.onled(bailling_3_led)
            functions.setcompletestate(path, 'state_1', 1, message, 0, 0)

        # niveau bailling 1 et 2 bas
        elif state_current == '001':
            message = "Bailling - ERREUR - niveau 1 et 2 bas"
            functions.offled(bailling_1_led)
            functions.offled(bailling_2_led)
            functions.onled(bailling_3_led)
            functions.setcompletestate(path, 'state_2', 1, message, 0, 0)

        # niveau bailling 1 et 3 bas
        elif state_current == '010':
            message = "Bailling - ERREUR - niveau 1 et 3 bas"
            functions.offled(bailling_1_led)
            functions.onled(bailling_2_led)
            functions.offled(bailling_3_led)
            functions.setcompletestate(path, 'state_3', 1, message, 0, 0)

        # niveau bailling 2 et 3 bas
        elif state_current == '100':
            message = "Bailling - ERREUR - niveau 2 et 3 bas"
            functions.onled(bailling_1_led)
            functions.offled(bailling_2_led)
            functions.offled(bailling_3_led)
            functions.setcompletestate(path, 'state_4', 1, message, 0, 0)

        # niveau bailling 1, 2 et 3 bas
        elif state_current == '000':
            message = "Bailling - ERREUR - niveau 1, 2 et 3 bas"
            functions.offled(bailling_1_led)
            functions.offled(bailling_2_led)
            functions.offled(bailling_3_led)
            functions.setcompletestate(path, 'state_5', 1, message, 0, 0)

        # niveau bailling 2 bas
        elif state_current == '101':
            message = "Bailling - ERREUR - niveau 2 bas"
            functions.onled(bailling_1_led)
            functions.offled(bailling_2_led)
            functions.onled(bailling_3_led)
            functions.setcompletestate(path, 'state_6', 1, message, 0, 0)

        # niveau bailling 3 bas
        elif state_current == '110':
            message = "Bailling - ERREUR - niveau 3 bas"
            functions.onled(bailling_1_led)
            functions.onled(bailling_2_led)
            functions.offled(bailling_3_led)
            functions.setcompletestate(path, 'state_7', 1, message, 0, 0)

        # niveau des 3 ok
        elif state_current == '111':
            message = "Bailling - OK"
            functions.onled(bailling_1_led)
            functions.onled(bailling_2_led)
            functions.onled(bailling_3_led)
            functions.setcompletestate(path, 'state_8', 0, message, 0, 0)

    functions.setcontrole('controle_bailling')
    sys.exit()

except Exception as e:
    message = "Bailling - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + " - " + str(e) + "</p>"
    functions.mail(message, body)
    functions.setcompletestate(path, 'state_9', 1, message + ' - ' + str(e), 0, 0)

    raise
