

#!/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime


debit_reacteur = functions.getconfig("debit_reacteur_min")

print(debit_reacteur)