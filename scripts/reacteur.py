#!/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime

FLOW_SENSOR = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(FLOW_SENSOR, GPIO.IN, pull_up_down=GPIO.PUD_UP)

global count
count = 0
path = 'reacteur'


def countpulse(channel):
    global count
    if start_counter == 1:
        count = count + 1


GPIO.add_event_detect(FLOW_SENSOR, GPIO.FALLING, callback=countpulse)

try:
    start_counter = 1
    time.sleep(10)
    start_counter = 0
    flow = int(round((count * 60 * 7.5 / 10)))
    flow = int(flow / 10)

    count = 0
    start_counter = 1
    time.sleep(10)
    start_counter = 0
    flow2 = int(round((count * 60 * 7.5 / 10)))
    flow2 = int(flow / 10)

    flow_min = flow * 0.90
    flow_max = flow * 1.10

    result = False

    if flow_min < flow2 < flow_max:

        if flow > 1050:
            message = "Reacteur - debit reacteur OK - " + str(flow) + " l/min"
            result = functions.setcompletestate(path, 'state_1', 0, message, 0, 0)

        if flow == 0:
            message = "Reacteur - ERREUR - debit reacteur nul - 0 l/min"
            result = functions.setcompletestate(path, 'state_2', 1, message, 0, 0)

        elif flow < 1050:
            message = "Reacteur - ERREUR - debit reacteur faible - " + str(flow) + " l/min"
            result = functions.setcompletestate(path, 'state_3', 1, message, 0, 0)

        # si on est toutes les 15 minutes on save en bdd la valeur
        nowMinute = datetime.datetime.now().strftime('%M')
        minute = int(nowMinute)
        if minute % 15 == 0 or result is True:
            functions.setdebit(flow)

    functions.setcontrole('controle_reacteur')
    sys.exit()

except Exception as e:
    message = "Reacteur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + " - " + str(e) + "</p>"
    functions.setcompletestate(path, 'state_4', 1, message + " - " + str(e), 1, 0)
    functions.mail(message, body)

    raise
