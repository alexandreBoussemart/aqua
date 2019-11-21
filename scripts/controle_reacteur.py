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
last_day2 = 0
last_day3 = 0
last_day4 = 0
in_to_0 = False
in_to_low = False
is_first = True
start_counter = 0
indentator = 0
minute = datetime.datetime.now().strftime('%M')


def countpulse(channel):
    global count
    if start_counter == 1:
        count = count + 1


GPIO.add_event_detect(FLOW_SENSOR, GPIO.FALLING, callback=countpulse)

while True:

    current = datetime.datetime.now().strftime('%M')
    if minute != current:
        functions.deletecontrole('controle_reacteur')
        functions.setcontrole('controle_reacteur')
        minute = current

    status = functions.getstatus('reacteur')
    if status == 0:
        continue

    try:
        message = ""
        start_counter = 1
        time.sleep(1)
        start_counter = 0
        flow = int(round((count * 60 * 7.5 / 10)))

        if indentator == 0:
            functions.setdebit(flow)

            functions.deletecontrole('controle_reacteur')
            functions.setcontrole('controle_reacteur')

            indentator = indentator + 1
        else:
            indentator = indentator + 1

        if indentator == 50:
            indentator = 0

        if flow > 0:
            time_to_0 = 0
            in_to_0 = False

        if flow > 1050:
            if in_to_low is True or in_to_0 is True or is_first is True:
                is_first = False
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

        count = 0
        time.sleep(5)

    except KeyboardInterrupt:
        print('End')
        sys.exit()

    except Exception as e:
        message = "Reacteur - ERREUR SCRIPT"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        functions.mail(message, body)

        raise