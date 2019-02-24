#! /usr/bin/python
# -*-coding:Latin-1 -*
import smtplib
import datetime
import sys
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from time import sleep
import functions

import RPi.GPIO as GPIO, time

last_day = 0

bailling_1 = 5
bailling_2 = 6
bailling_3 = 13
bailling_1_led = 19
bailling_2_led = 26
bailling_3_led = 12

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

            # niveau bailling 1 et 2 bas
            elif state_current == '001':
                message = "Bailling - ERREUR - niveau 1 et 2 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_2 = 0
                functions.offled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.onled(bailling_3_led)

            # niveau bailling 1 et 3 bas
            elif state_current == '010':
                message = "Bailling - ERREUR - niveau 1 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_3 = 0
                functions.offled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.offled(bailling_3_led)

            # niveau bailling 2 et 3 bas
            elif state_current == '100':
                message = "Bailling - ERREUR - niveau 2 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_2_3 = 0
                functions.onled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.offled(bailling_3_led)

            # niveau bailling 1, 2 et 3 bas
            elif state_current == '000':
                message = "Bailling - ERREUR - niveau 1, 2 et 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_1_2_3 = 0
                functions.offled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.offled(bailling_3_led)

            # niveau bailling 2 bas
            elif state_current == '101':
                message = "Bailling - ERREUR - niveau 2 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_2 = 0
                functions.onled(bailling_1_led)
                functions.offled(bailling_2_led)
                functions.onled(bailling_3_led)

            # niveau bailling 3 bas
            elif state_current == '110':
                message = "Bailling - ERREUR - niveau 3 bas"
                body = "<p style='color:red;'>" + message + "</p>"
                time_bailling_3 = 0
                functions.onled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.offled(bailling_3_led)

            # niveau des 3 ok
            elif state_current == '111':
                message = "Bailling - OK"
                body = "<p style='color:green;'>" + message + "</p>"
                functions.onled(bailling_1_led)
                functions.onled(bailling_2_led)
                functions.onled(bailling_3_led)

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
                print("1",time_bailling_1)

                # rappel 30 minutes
                if time_bailling_1 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 bas"
                    time_bailling_1 = 0

            # niveau bailling 1 et 2 bas
            elif state_current == '001':
                time_bailling_1_2 = time_bailling_1_2 + 1
                print("12", time_bailling_1_2)

                # rappel 30 minutes
                if time_bailling_1_2 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 et 2 bas"
                    time_bailling_1_2 = 0

            # niveau bailling 1 et 3 bas
            elif state_current == '010':
                time_bailling_1_3 = time_bailling_1_3 + 1
                print("13", time_bailling_1_3)

                # rappel 30 minutes
                if time_bailling_1_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1 et 3 bas"
                    time_bailling_1_3 = 0

            # niveau bailling 2 et 3 bas
            elif state_current == '100':
                time_bailling_2_3 = time_bailling_2_3 + 1
                print("23", time_bailling_2_3)

                # rappel 30 minutes
                if time_bailling_2_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 2 et 3 bas"
                    time_bailling_2_3 = 0

            # niveau bailling 1, 2 et 3 bas
            elif state_current == '000':
                time_bailling_1_2_3 = time_bailling_1_2_3 + 1
                print("123", time_bailling_1_2_3)

                # rappel 30 minutes
                if time_bailling_1_2_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 1, 2 et 3 bas"
                    time_bailling_1_2_3 = 0

            # niveau bailling 2 bas
            elif state_current == '101':
                time_bailling_2 = time_bailling_2 + 1
                print("2", time_bailling_2)

                # rappel 30 minutes
                if time_bailling_2 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 2 bas"
                    time_bailling_2 = 0

            # niveau bailling 3 bas
            elif state_current == '110':
                time_bailling_3 = time_bailling_3 + 1
                print("3", time_bailling_3)

                # rappel 30 minutes
                if time_bailling_3 > 180000:
                    message = "Bailling - RAPPEL ERREUR - niveau 3 bas"
                    time_bailling_3 = 0

            if message != "":
                print(message)
                body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"

                # envoie mail rappel
                functions.mail(message, body)

        # on envoie un mail de controle tous les jours  8h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '0800' and last_day != day:
            message = "Bailling - controle 8h OK"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day

except KeyboardInterrupt:
    print('End')
    GPIO.cleanup()
    sys.exit()

except:
    message = "Bailling - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>"+message+"</p>"
    print(message)
    functions.mail(message, body)
    print "Unexpected error:", sys.exc_info()[0]

    raise
