#! /usr/bin/python
# -*-coding:Latin-1 -*
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import RPi.GPIO as GPIO
import smtplib
import mysql.connector


def connect():
    with open('/home/pi/Desktop/aqua/config.json') as f:
        data = json.load(f)

    return mysql.connector.connect(
        host=data["database"][0]["host"],
        user=data["database"][0]["user"],
        passwd=data["database"][0]["passwd"],
        database=data["database"][0]["database"]
    )


def getstatus(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        mycursor.execute("SELECT `value` FROM `config` WHERE `name` = '" + value + "' LIMIT 1")
        myresult = mycursor.fetchone()[0]
        mydb.close()

        return myresult

    except Exception as e:
        message = "SQL - ERREUR getstatus"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def settemperature(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        temp = str(value)

        sql = "INSERT INTO `temperature`( `value`) VALUES (" + temp + ")"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR settemperature"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setcontrole(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        val = str(value)

        sql = "INSERT INTO `controle`( `value`) VALUES ('" + val + "')"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR setcontrole"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def deletecontrole(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        val = str(value)

        sql = "DELETE FROM `controle` WHERE value='" + val + "'"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR deletecontrole"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def deletestate(path):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        path = str(path)

        sql = "DELETE FROM `state` WHERE value='" + path + "'"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR deletestate"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setstate(path, value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        path = str(path)
        value = str(value)

        sql = "INSERT INTO `state`( `path`,`value`) VALUES ('" + path + "', '" + value + "')"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR setstate"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setdebit(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        debit = str(value)

        sql = "INSERT INTO `reacteur`( `value`) VALUES (" + debit + ")"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR setdebit"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)
        raise


def mail(m, b):
    try:
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

    except Exception as e:
        message = "Mail - ERREUR mail"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)
        raise


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
