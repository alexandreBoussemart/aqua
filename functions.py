#! /usr/bin/python
# -*-coding:Latin-1 -*
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import RPi.GPIO as GPIO
import smtplib


def mail(m, b):
    with open('/home/pi/Desktop/aqua/config.json') as f:
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


def read_file(emplacement):
    file = open(emplacement)
    content = file.read()
    file.close()
    return content


def get_temp(content):
    first_line = content.split("\n")[0]
    if first_line.find('NO') != -1:
        return False

    second_line = content.split("\n")[1]
    temp = second_line.split(" ")[9]
    temperature = temp[2:]
    if temperature == "85000":
        return False

    temperature = float(temperature)
    temperature = temperature / 1000
    return temperature

