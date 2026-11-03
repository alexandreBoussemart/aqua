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

relay = 11

functions.on_relay(relay)

if os.path.exists(os.path.join(BASE_DIR, "var", "relais", "ecumeur")):
  os.remove(os.path.join(BASE_DIR, "var", "relais", "ecumeur"))

sys.exit()
