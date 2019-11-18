#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep
import functions

import RPi.GPIO as GPIO

relais = 16

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

GPIO.setup(bailling_1, GPIO.IN, pull_up_down=GPIO.PUD_UP)
functions.onled(relais)
