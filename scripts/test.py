

#!/usr/bin/python
# -*-coding:Latin-1 -*
import RPi.GPIO as GPIO
import time, sys
import functions
import datetime
from pathlib import Path
import os

currentDirectory = os.getcwd()
Path(currentDirectory+'../state/file.txt').touch()
