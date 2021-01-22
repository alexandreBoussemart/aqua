#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import RPi.GPIO as GPIO
import os

relay = 20

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.on_relay(relay)

if os.path.exists("/home/pi/Desktop/www/aqua/statusRelais/refroidissement") is False:
    f = open("/home/pi/Desktop/www/aqua/statusRelais/refroidissement", "w")

sys.exit()
