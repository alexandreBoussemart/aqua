#! /usr/bin/python
# -*-coding:Latin-1 -*
import sys
from time import sleep

sys.path.insert(1, '/home/pi/Desktop/www/aqua/scripts/')
import functions
import RPi.GPIO as GPIO
import os

relay = 11

functions.on_relay(relay)

if os.path.exists("/home/pi/Desktop/www/aqua/statusRelais/ecumeur"):
  os.remove("/home/pi/Desktop/www/aqua/statusRelais/ecumeur")

sys.exit()
