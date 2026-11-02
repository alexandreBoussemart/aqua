#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import sys
from time import sleep

import os
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..'))
sys.path.insert(1, os.path.join(BASE_DIR, 'scripts'))
import functions
import RPi.GPIO as GPIO
import os

relay = 9

functions.on_relay(relay)

if os.path.exists(os.path.join(BASE_DIR, "statusRelais/aquarium_ventilateur")) is False:
    open(os.path.join(BASE_DIR, "statusRelais/aquarium_ventilateur"), "w").close()

sys.exit()
