#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep
import ../functions

import RPi.GPIO as GPIO

# à modifier trouver port libre
relais = 99

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.offled(relais)
sys.exit()
