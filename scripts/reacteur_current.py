#!/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime

FLOW_SENSOR = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(FLOW_SENSOR, GPIO.IN, pull_up_down=GPIO.PUD_UP)

global count
count = 0
path = 'reacteur'
start_counter = 0


def countpulse(channel):
    global count
    if start_counter == 1:
        count = count + 1


GPIO.add_event_detect(FLOW_SENSOR, GPIO.FALLING, callback=countpulse)


start_counter = 1
time.sleep(10)
start_counter = 0
flow = int(round((count * 60 * 7.5 / 10)))
flow = int(flow / 10)

print(str(flow) + " l/min")


