#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '../')
import functions

import RPi.GPIO as GPIO

relais = 20

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.onled(relais)
sys.exit()
