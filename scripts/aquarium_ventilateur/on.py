#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import RPi.GPIO as GPIO

relais = 9

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
functions.onled(relais)

f = open("../statusRelais/aquarium_ventilateur", "w")

sys.exit()
