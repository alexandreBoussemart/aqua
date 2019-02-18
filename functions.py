#! /usr/bin/python
# -*-coding:Latin-1 -*

import json
import sys
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from pprint import pprint
import RPi.GPIO as GPIO
import smtplib


def mail(m, b):
    with open('config.json') as f:
        data = json.load(f)

    fromaddr = data["gmail"][0]["mail"]
    password = data["gmail"][0]["password"]
    port = data["gmail"][0]["port"]
    server = data["gmail"][0]["server"]
    toaddr = data["mail_to"]

    server = smtplib.SMTP(server, port)
    server.starttls()
    server.login(fromaddr, password)

    msg = MIMEMultipart()
    msg['From'] = fromaddr
    msg['To'] = toaddr

    msg.attach(MIMEText(b, 'html'))
    msg['Subject'] = m
    text = msg.as_string()
    server.sendmail(fromaddr, toaddr, text)
    server.quit()


def offled(led):
    GPIO.setup(led, GPIO.OUT)
    GPIO.output(led, 0)
    GPIO.cleanup(led)


def onled(led):
    GPIO.setup(led, GPIO.OUT)
    GPIO.output(led, 1)


def stoppump(r):
    GPIO.setup(r, GPIO.OUT)
    GPIO.output(r, 0)
    GPIO.cleanup(r)