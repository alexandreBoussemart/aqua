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
relais = 21
to_high = 23
level_ok = 24
pump_on = 25
to_low = 27
init = 0

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

try:
    GPIO.setup(to_high, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    TO_HIGH_STATE = 0
    time_to_high = 0

    GPIO.setup(to_low, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    TO_LOW_STATE = 0
    time_to_low = 0

    GPIO.setup(pump_on, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    PUMP_ON_STATE = 0
    state_relais = False
    time_pump_on = 0

    GPIO.setup(level_ok, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    LEVEL_OK_STATE = 0

    time_off = 0
    state = '2222'
    message = ''
    body = ''

    while True:
        status = functions.getstatus('controle_osmolateur')
        if status == 0:
            continue

        sleep(1 / 100)

        # lecture GPIO led to high
        TO_HIGH_STATE = str(GPIO.input(to_high))

        # lecture GPIO led to low
        TO_LOW_STATE = str(GPIO.input(to_low))

        # lecture GPIO led pump on
        PUMP_ON_STATE = str(GPIO.input(pump_on))

        # lecture GPIO led level ok
        LEVEL_OK_STATE = str(GPIO.input(level_ok))

        # on fait la chaine de caractère du statut
        state_current = TO_HIGH_STATE + LEVEL_OK_STATE + PUMP_ON_STATE + TO_LOW_STATE

        # si changement de statut
        if state_current != state:

            # niveau d'eau TO HIGHT
            if state_current == '0111':
                message = "Osmolateur - ERREUR - niveau d'eau TO HIGHT"
                body = "<p style='color:red;'>" + message + "</p>"
                functions.stoppump(relais)
                time_to_high = 0
                functions.setosmolateur("to_high")

                functions.deletecontrole('controle_osmolateur')
                functions.setcontrole('controle_osmolateur')

            # niveau d'eau OK
            elif state_current == '1011':
                message = "Osmolateur - niveau d'eau OK"
                body = "<p style='color:green;'>" + message + "</p>"
                functions.stoppump(relais)
                state_relais = False
                functions.setosmolateur("ok")

                functions.deletecontrole('controle_osmolateur')
                functions.setcontrole('controle_osmolateur')

            # remplissage en cours
            elif state_current == '1101':
                message = "Osmolateur - remplissage en cours"
                body = "<p style='color:orange;'>" + message + "</p>"
                GPIO.setup(relais, GPIO.OUT)
                GPIO.output(relais, 1)
                time_pump_on = 0
                functions.setosmolateur("pump_on")

                functions.deletecontrole('controle_osmolateur')
                functions.setcontrole('controle_osmolateur')

            # niveau d'eau TO LOW
            elif state_current == '1110':
                message = "Osmolateur - ERREUR - niveau d'eau TO LOW"
                body = "<p style='color:red;'>" + message + "</p>"
                functions.stoppump(relais)
                time_to_low = 0
                functions.setosmolateur("to_low")

                functions.deletecontrole('controle_osmolateur')
                functions.setcontrole('controle_osmolateur')

            # osmolateur off
            elif state_current == '1111':
                message = ""
                functions.stoppump(relais)
                time_off = 0

            else:
                continue

            state = state_current

            if message != "":
                print(message)
                # envoie mail changement du statut
                functions.mail(message, body)

        # si pas de changement de statut
        else:
            message = ""

            # niveau d'eau TO HIGHT
            if state_current == '0111':
                time_to_high = time_to_high + 1

                # rappel 30 minutes
                if time_to_high > 180000:
                    message = "Osmolateur - RAPPEL ERREUR - niveau d'eau TO HIGH"
                    time_to_high = 0
                    functions.setosmolateur("to_high_rappel")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

            # remplissage en cours
            elif state_current == '1101':
                time_pump_on = time_pump_on + 1

                # si pompe allumé depuis plus de 20secondes
                if time_pump_on > 2000 and state_relais is False:
                    state_relais = True
                    message = "Osmolateur - ERREUR - pompe allumée plus de 20 secondes"
                    functions.stoppump(relais)
                    functions.setosmolateur("pump_on_20")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

                # si pompe allumé depuis plus de 20secondes rappel 30 minutes
                if time_pump_on > 180000:
                    message = "Osmolateur - RAPPEL ERREUR - pompe allumée plus de 20 secondes"
                    time_pump_on = 0
                    functions.setosmolateur("pump_on_20_rappel")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

            # niveau d'eau TO LOW
            elif state_current == '1110':
                time_to_low = time_to_low + 1

                # rappel 10 minutes
                if time_to_low > 180000:
                    message = "Osmolateur - RAPPEL ERREUR - niveau d'eau TO LOW"
                    time_to_low = 0
                    functions.setosmolateur("to_low_rappel")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

            # osmolateur off
            elif state_current == '1111':
                time_off = time_off + 1

                # alert off a partir de 5 secondes en off
                if time_off == 500:
                    message = "Osmolateur - ERREUR - off"
                    functions.setosmolateur("off")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

                # rappel 30 minutes
                if time_off > 540000:
                    message = "Osmolateur - RAPPEL ERREUR - off"
                    time_off = 0
                    functions.setosmolateur("off_rappel")

                    functions.deletecontrole('controle_osmolateur')
                    functions.setcontrole('controle_osmolateur')

            if message != "":
                print(message)
                body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"

                # envoie mail rappel
                functions.mail(message, body)

        # on envoie un mail de controle tous les jours à 8h et on et un controle à 18h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '1759' and last_day3 != day:
            functions.deletecontrole('controle_osmolateur')
            last_day3 = day

        if now == '1800' and last_day4 != day:
            functions.setcontrole('controle_osmolateur')
            last_day4 = day

        if now == '0759' and last_day2 != day:
            functions.deletecontrole('controle_osmolateur')
            last_day2 = day

        if now == '0800' and last_day != day:
            message = "Osmolateur - controle 8h OK"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day
            functions.setcontrole('controle_osmolateur')

        if init == 0:
            functions.deletecontrole('controle_osmolateur')
            functions.setcontrole('controle_osmolateur')
            init = 1

except KeyboardInterrupt:
    print('End')
    sys.exit()

except Exception as e:
    message = "Osmolateur - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
    print(message)
    functions.mail(message, body)
    functions.stoppump(relais)

    raise
