# !/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime
import os
import os.path
from os import path

os.system('rm ' + os.getcwd() + '/../state/' + 'osmo' + '*')
os.system("touch " + os.getcwd() + '/../state/' + "osmo" + '-' + "test-1")
print(path.exists(os.getcwd() + '/../state/' + "osmo" + '-' + "test-1"))