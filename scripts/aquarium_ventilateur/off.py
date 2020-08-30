#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import os
import RPi.GPIO as GPIO

relais = 9

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.offled(relais)

if os.path.exists("/home/pi/Desktop/www/aqua/statusRelais/aquarium_ventilateur"):
  os.remove("/home/pi/Desktop/www/aqua/statusRelais/aquarium_ventilateur")

sys.exit()
