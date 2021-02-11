# !/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime
import os, glob
import os.path

bailling_1 = 8
bailling_2 = 22
bailling_3 = 13

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

GPIO.setup(bailling_1, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(bailling_2, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(bailling_3, GPIO.IN, pull_up_down=GPIO.PUD_UP)

# lecture GPIO bailling_1
BAILLING_1_STATE = str(GPIO.input(bailling_1))

# lecture GPIO bailling_2
BAILLING_2_STATE = str(GPIO.input(bailling_2))

# lecture GPIO bailling_3
BAILLING_3_STATE = str(GPIO.input(bailling_3))

# on fait la chaine de caractère du statut
state_current = BAILLING_1_STATE + BAILLING_2_STATE + BAILLING_3_STATE

print(state_current)

