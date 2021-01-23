#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import RPi.GPIO as GPIO
import os

relay = 21

functions.on_relay(relay)

if os.path.exists("/home/pi/Desktop/www/aqua/statusRelais/pompe_osmolateur") is False:
    f = open("/home/pi/Desktop/www/aqua/statusRelais/pompe_osmolateur", "w")

sys.exit()
