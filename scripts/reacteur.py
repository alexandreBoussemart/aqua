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
    time.sleep(20)
    start_counter = 0
    flow = int(round((count * 60 * 7.5 / 10)))
    flow = int(flow / 20)

    if flow > 1050:
        message = "Reacteur - debit reacteur OK"
        functions.setcompletestate(path, 'state_1', 0, message, 0, 0)

    if flow == 0:
        message = "Reacteur - ERREUR - debit reacteur = 0"
        functions.setcompletestate(path, 'state_2', 1, message, 0, 0)

    if flow < 1050:
        message = "Reacteur - ERREUR - debit reacteur faible"
        functions.setcompletestate(path, 'state_3', 1, message, 0, 0)

    # si on est toutes les 5 minutes on save en bdd la valeur
    nowMinute = datetime.datetime.now().strftime('%M')
    if nowMinute % 5 == 0:
        functions.setdebit(flow)

    functions.setcontrole('controle_reacteur')
    sys.exit()

except Exception as e:
    message = "Reacteur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    functions.setcompletestate(path, 'state_4', 1, message + ' - ' + str(e), 1, 0)
    functions.mail(message, body)

    raise