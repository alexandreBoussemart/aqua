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
start_counter = 0


def countpulse(channel):
    global count
    if start_counter == 1:
        count = count + 1


GPIO.add_event_detect(FLOW_SENSOR, GPIO.FALLING, callback=countpulse)

try:
    start_counter = 1
    time.sleep(1)
    start_counter = 0
    flow = int(round((count * 60 * 7.5 / 10)))

    if flow > 1050:
        message = "Reacteur - debit reacteur OK"

    if flow == 0:
        message = "Reacteur - ERREUR - debit reacteur = 0"

    if flow < 1050:
        message = "Reacteur - ERREUR - debit reacteur faible"

    print(message)
    print(flow)


except Exception as e:
    message = "Reacteur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    print(message)
    #functions.mail(message, body)

    raise
