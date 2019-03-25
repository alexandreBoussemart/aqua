#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import sys
from time import sleep
import functions

import RPi.GPIO as GPIO

last_day = 0
last_day2 = 0
last_day3 = 0
last_day4 = 0
port = 18

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(port, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    time_error = 0

    state = '2'
    message = ''
    body = ''

    while True:
        status = functions.getstatus('controle_ecumeur')
        if status == 0:
            continue

        sleep(1 / 100)

        # lecture GPIO led to high
        state_current = str(GPIO.input(port))

        # si changement de statut
        if state_current != state:

            # niveau �cumeur eau trop eau
            if state_current == '0':
                message = "Ecumeur - ERREUR - niveau godet trop haut"
                body = "<p style='color:red;'>" + message + "</p>"
                time_error = 0

            # niveau eau ok
            elif state_current == '1':
                message = "Ecumeur - niveau godet OK"
                body = "<p style='color:green;'>" + message + "</p>"
                time_error = 0

            else:
                state = '2'
                continue

            state = state_current
            print(message)

            # envoie mail changement du statut
            functions.mail(message, body)

        # si pas de changement de statut
        else:
            message = ""

            # niveau d'eau TO HIGHT
            if state_current == '0':
                time_error = time_error + 1

                # rappel 30 minutes
                if time_error > 180000:
                    message = "Ecumeur - RAPPEL ERREUR - niveau godet trop haut"
                    time_error = 0

            if message != "":
                print(message)
                body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"

                # envoie mail rappel
                functions.mail(message, body)

        # on envoie un mail de controle tous les jours  8h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '1759' and last_day3 != day:
            functions.deletecontrole('controle_ecumeur')
            last_day3 = day

        if now == '1800' and last_day4 != day:
            functions.setcontrole('controle_ecumeur')
            last_day4 = day

        if now == '0759' and last_day2 != day:
            functions.deletecontrole('controle_ecumeur')
            last_day2 = day

        if now == '0800' and last_day != day:
            message = "Ecumeur - controle 8h OK"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day
            functions.setcontrole('controle_ecumeur')

except KeyboardInterrupt:
    print('End')
    sys.exit()

except:
    message = "Ecumeur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"
    print(message)
    functions.mail(message, body)

    raise
