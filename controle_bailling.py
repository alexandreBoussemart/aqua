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
bailling_1 = 5
bailling_2 = 22
bailling_3 = 13
bailling_1_led = 19
bailling_2_led = 26
bailling_3_led = 12
init = 0
minute = datetime.datetime.now().strftime('%M')

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(bailling_1, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    BAILLING_1_STATE = 0
    time_bailling_1 = 0

    GPIO.setup(bailling_2, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    BAILLING_2_STATE = 0
    time_bailling_2 = 0

    GPIO.setup(bailling_3, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    BAILLING_3_STATE = 0
    time_bailling_3 = 0

    time_bailling_1_2 = 0
    time_bailling_1_3 = 0
    time_bailling_2_3 = 0
    time_bailling_1_2_3 = 0

    state = '222'
    message = ''
    body = ''

    while True:

        current = datetime.datetime.now().strftime('%M')
        if minute != current:
            functions.deletecontrole('controle_bailling')
            functions.setcontrole('controle_bailling')
            minute = current

        status = functions.getstatus('controle_bailling')
        if status == 0:
            continue

        sleep(1 / 100)

        # lecture GPIO bailling_1
        BAILLING_1_STATE = str(GPIO.input(bailling_1))

        # lecture GPIO bailling_2
        BAILLING_2_STATE = str(GPIO.input(bailling_2))

        # lecture GPIO bailling_3
        BAILLING_3_STATE = str(GPIO.input(bailling_3))

        # on fait la chaine de caractère du statut
        state_current = BAILLING_1_STATE+BAILLING_2_STATE+BAILLING_3_STATE

        # si changement de statut
        if state_current != state:

            # niveau bailling 1 bas
            if state_current == '011':
                message = "Bailling - ERREUR - niveau 1 bas"
                body = "<p style='color:red;'>"+message+"</p>"
                time_bailling_1 = 0
                functions.offled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.onled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling',state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 1 et 2 bas
            elif state_current == '001':
                message = "Bailling - ERREUR - niveau 1 et 2 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_2 = 0
                functions.offled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.onled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 1 et 3 bas
            elif state_current == '010':
                message = "Bailling - ERREUR - niveau 1 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_3 = 0
                functions.offled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.offled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 2 et 3 bas
            elif state_current == '100':
                message = "Bailling - ERREUR - niveau 2 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_2_3 = 0
                functions.onled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.offled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 1, 2 et 3 bas
            elif state_current == '000':
                message = "Bailling - ERREUR - niveau 1, 2 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_2_3 = 0
                functions.offled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.offled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 2 bas
            elif state_current == '101':
                message = "Bailling - ERREUR - niveau 2 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_2 = 0
                functions.onled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.onled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau bailling 3 bas
            elif state_current == '110':
                message = "Bailling - ERREUR - niveau 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_3 = 0
                functions.onled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.offled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            # niveau des 3 ok
            elif state_current == '111':
                message = "Bailling - OK"
                body = "<p style='color:green;'>" + message + "</p>"
                functions.onled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.onled(bailling_3_led)

                functions.deletestate('bailling')
                functions.setstate('bailling', state_current)

                functions.deletecontrole('controle_bailling')
                functions.setcontrole('controle_bailling')

            else:
                state = '222'
                continue

            state = state_current
            print(message)

            # envoie mail changement du statut
            functions.mail(message, body)

        # si pas de changement de statut
        else:
            message = ""

            # niveau bailling 1 bas
            if state_current == '011':
                time_bailling_1 = time_bailling_1 + 1

                # rappel 30 minutes
                if time_bailling_1 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 bas"
                    time_bailling_1 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 1 et 2 bas
            elif state_current == '001':
                time_bailling_1_2 = time_bailling_1_2 + 1

                # rappel 30 minutes
                if time_bailling_1_2 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 et 2 bas"
                    time_bailling_1_2 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 1 et 3 bas
            elif state_current == '010':
                time_bailling_1_3 = time_bailling_1_3 + 1

                # rappel 30 minutes
                if time_bailling_1_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 et 3 bas"
                    time_bailling_1_3 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 2 et 3 bas
            elif state_current == '100':
                time_bailling_2_3 = time_bailling_2_3 + 1

                # rappel 30 minutes
                if time_bailling_2_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 2 et 3 bas"
                    time_bailling_2_3 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 1, 2 et 3 bas
            elif state_current == '000':
                time_bailling_1_2_3 = time_bailling_1_2_3 + 1

                # rappel 30 minutes
                if time_bailling_1_2_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1, 2 et 3 bas"
                    time_bailling_1_2_3 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 2 bas
            elif state_current == '101':
                time_bailling_2 = time_bailling_2 + 1

                # rappel 30 minutes
                if time_bailling_2 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 2 bas"
                    time_bailling_2 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            # niveau bailling 3 bas
            elif state_current == '110':
                time_bailling_3 = time_bailling_3 + 1

                # rappel 30 minutes
                if time_bailling_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 3 bas"
                    time_bailling_3 = 0
                    functions.deletestate('bailling')
                    functions.setstate('bailling', state_current)

                    functions.deletecontrole('controle_bailling')
                    functions.setcontrole('controle_bailling')

            if message != "":
                print(message)
                body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"

                # envoie mail rappel
                functions.mail(message, body)

        # on envoie un mail de controle tous les jours à 8h et on et un controle à 18h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '1759' and last_day3 != day:
            functions.deletecontrole('controle_bailling')
            last_day3 = day

        if now == '1800' and last_day4 != day:
            functions.setcontrole('controle_bailling')
            last_day4 = day

        if now == '0759' and last_day2 != day:
            functions.deletecontrole('controle_bailling')
            last_day2 = day

        if now == '0800' and last_day != day:
            message = "Bailling - controle 8h OK"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day
            functions.setcontrole('controle_bailling')

        if init == 0:
            functions.deletecontrole('controle_bailling')
            functions.setcontrole('controle_bailling')
            init = 1

except KeyboardInterrupt:
    print('End')
    sys.exit()

except Exception as e:
    message = "Bailling - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    print(message)
    functions.mail(message, body)

    raise
