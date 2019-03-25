#! /usr/bin/python
# -*-coding:Latin-1 -*
import datetime
import sys
from time import sleep
import functions

error_min = False
time_error_min = 0
error_max = False
time_error_max = 0
last_day = 0
last_day2 = 0
last_day3 = 0
last_day4 = 0
temp_ok = False
second = 10
temperature = 1

try:
    while True:
        status = functions.getstatus('controle_temperature')
        if status == 0:
            continue

        if second == 10:
            second = 1
            content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
            temperature = functions.get_temp(content)
            message = ""

            if temperature is False:
                continue

            if temperature < 23:
                temp_min = temperature * 0.95
                temp_max = temperature * 1.05

                content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
                temperature2 = functions.get_temp(content)

                functions.settemperature(temperature2)

                if temp_min < temperature2 < temp_max:
                    temp_ok = False

                    if error_min is False:
                        message = "Temperature - ERREUR - trop froid "+str(temperature)+"°C"

                    error_min = True
                    time_error_min = time_error_min + 1

                    if time_error_min > 18000 and error_min is True:
                        time_error_min = 0
                        message = "Temperature - RAPPEL ERREUR - trop froid "+str(temperature)+"°C"

            elif temperature > 28:
                temp_min = temperature * 0.95
                temp_max = temperature * 1.05

                content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
                temperature2 = functions.get_temp(content)

                functions.settemperature(temperature2)

                if temp_min < temperature2 < temp_max:

                    temp_ok = False

                    if error_max is False:
                        message = "Temperature - ERREUR - trop chaud "+str(temperature)+"°C"

                    error_max = True
                    time_error_max = time_error_max + 1

                    if time_error_max > 18000 and error_max is True:
                        time_error_max = 0
                        message = "Temperature - RAPPEL ERREUR - trop chaud "+str(temperature)+"°C"

            else:
                if temp_ok is False:
                    message = "Temperature - OK -  " + str(temperature) + "°C"

                functions.settemperature(temperature)

                temp_ok = True
                error_min = False
                time_error_min = 0
                error_max = False
                time_error_max = 0

            if message != "" and temp_ok is False:
                print(message)
                body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"
                functions.mail(message, body)

            if message != "" and temp_ok is True:
                print(message)
                body = "<p style='color:green;'>" + message + "</p>"
                functions.mail(message, body)

        # on envoie un mail de controle tous les jours à 8h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '1759' and last_day3 != day:
            functions.deletecontrole('controle_temperature')
            last_day3 = day

        if now == '1800' and last_day4 != day:
            functions.setcontrole('controle_temperature')
            last_day4 = day

        if now == '0759' and last_day2 != day:
            functions.deletecontrole('controle_temperature')
            last_day2 = day

        if now == '0800' and last_day != day:
            message = "Temperature - controle 8h OK - "+str(temperature)+"°C"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day
            functions.setcontrole('controle_temperature')

        second = second + 1
        sleep(1)

except KeyboardInterrupt:
    print('End')
    sys.exit()

except:
    message = "Temperature - ERREUR SCRIPT"
    body = "<p style='color:red;text-transform:uppercase;'>" + message + "</p>"
    print(message)
    functions.mail(message, body)

    raise
