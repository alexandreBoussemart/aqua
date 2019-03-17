#!/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime

FLOW_SENSOR = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(FLOW_SENSOR, GPIO.IN, pull_up_down = GPIO.PUD_UP)

global count
count = 0
time_to_0 = 0
time_to_low = 0
last_day = 0
in_to_0 = True
in_to_low = True


def countpulse(channel):
    global count
    if start_counter == 1:
        count = count + 1


GPIO.add_event_detect(FLOW_SENSOR, GPIO.FALLING, callback=countpulse)

while True:
    try:
        message = ""
        start_counter = 1
        time.sleep(1)
        start_counter = 0
        flow = int(round((count * 60 * 7.5 / 10)))

        if flow > 0:
            time_to_0 = 0
            in_to_0 = False

        if flow > 1050:
            if in_to_low is True or in_to_0 is True:
                message = "Reacteur - debit reacteur OK"
                body = "<p style='color:green;'>" + message + "</p>"
                print(message)
                functions.mail(message, body)
                message = ""

            time_to_low = 0
            in_to_low = False

        if flow == 0:

            if time_to_0 == 0 and in_to_0 is False:
                message = "Reacteur - ERREUR - debit reacteur = 0"
                in_to_0 = True

            time_to_0 = time_to_0 + 6

            if time_to_0 > 1800:
                message = "Reacteur - RAPPEL ERREUR - debit reacteur = 0"
                time_to_0 = 0

        elif flow < 1050:

            if time_to_low == 0 and in_to_low is False:
                message = "Reacteur - ERREUR - debit reacteur faible"
                in_to_low = True

            time_to_low = time_to_low + 6

            if time_to_low > 1800:
                message = "Reacteur - RAPPEL ERREUR - debit reacteur faible"
                time_to_low = 0

        if message != "":
            print(message)
            body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"

            # envoie mail erreur
            functions.mail(message, body)

        # on envoie un mail de controle tous les jours  8h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '0800' and last_day != day:
            message = "Reacteur - controle 8h OK - " + flow
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day

        count = 0
        time.sleep(5)

    except KeyboardInterrupt:
        print('End')
        sys.exit()

    except:
        message = "Reacteur - ERREUR SCRIPT"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"
        print(message)
        functions.mail(message, body)

        raise