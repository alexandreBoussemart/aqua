# !/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime
import os

os.system('rm ' + os.getcwd() + '/../state/' + 'osmo' + '*')
os.system("touch " + os.getcwd() + '/../state/' + "osmo" + '-' + "test-1")
