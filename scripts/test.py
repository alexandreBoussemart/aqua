# !/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime
import os, glob
import os.path


currentDir = os.path.dirname(os.path.realpath(__file__))

for filename in glob.glob(currentDir + '/../state/' + "osmo" + '-' + "*"):
    os.remove(filename)

file = currentDir + '/../state/' + "osmo" + '-' + "test-2"

os.mknod(file)
print(os.path.isfile(file))