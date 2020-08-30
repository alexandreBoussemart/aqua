#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import os
import RPi.GPIO as GPIO

relais = 16

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.offled(relais)

if os.path.exists("../statusRelais/reacteur_ventilateur"):
  os.remove("../statusRelais/reacteur_ventilateur")

sys.exit()
