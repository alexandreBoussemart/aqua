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
temp_ok = False
second = 5
temperature = 1

try:
    while True:
        if second == 5:
            second = 1
            content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
            temperature = functions.get_temp(content)
            message = ""

            if temperature is False:
                continue

            if temperature < 23:

                temp_min = temperature * 0.85
                temp_max = temperature * 1.15

                content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
                temperature = functions.get_temp(content)

                if temp_min < temperature < temp_max:
                    temp_ok = False

                    if error_min is False:
                        message = "Temperature - ERREUR - trop froid "+str(temperature)+"°C"

                    error_min = True
                    time_error_min = time_error_min + 1

                    if time_error_min > 180000 and error_min is True:
                        time_error_min = 0
                        message = "Temperature - RAPPEL ERREUR - trop froid "+str(temperature)+"°C"

            elif temperature > 28:

                temp_min = temperature * 0.85
                temp_max = temperature * 1.15

                content = functions.read_file("/sys/bus/w1/devices/28-01142f1e02d2/w1_slave")
                temperature = functions.get_temp(content)

                if temp_min < temperature < temp_max:

                    temp_ok = False

                    if error_max is False:
                        message = "Temperature - ERREUR - trop chaud "+str(temperature)+"°C"

                    error_max = True
                    time_error_max = time_error_max + 1

                    if time_error_max > 180000 and error_max is True:
                        time_error_max = 0
                        message = "Temperature - RAPPEL ERREUR - trop chaud "+str(temperature)+"°C"

            else:
                if temp_ok is False:
                    message = "Temperature - OK -  " + str(temperature) + "°C"

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

        # on envoie un mail de controle tous les jours  8h
        now = datetime.datetime.now().strftime('%H%M')
        day = datetime.datetime.now().strftime('%d')

        if now == '0800' and last_day != day:
            message = "Temperature - controle 8h OK - "+str(temperature)+"°C"
            body = "<p style='color:blue;'>" + message + "</p>"
            print(message)
            functions.mail(message, body)
            last_day = day

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
