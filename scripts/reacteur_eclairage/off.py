#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '../')
import functions

import RPi.GPIO as GPIO

relais = 6

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.offled(relais)
sys.exit()
