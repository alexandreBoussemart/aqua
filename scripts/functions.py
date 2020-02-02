#! /usr/bin/python
# -*-coding:Latin-1 -*
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import RPi.GPIO as GPIO
import smtplib
import mysql.connector
import os


def connect():
    with open('/home/pi/Desktop/www/aqua/config.json') as f:
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
        mycursor.execute("SELECT `value` FROM `status` WHERE `name` = '" + value + "' LIMIT 1")
        myresult = mycursor.fetchone()[0]
        mydb.close()

        return myresult

    except Exception as e:
        message = "SQL - ERREUR - getstatus"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setcontrole(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        val = str(value)

        sql = "UPDATE `last_activity` set `value`='" + val + "', `created_at`=now() WHERE `value`='" + val + "'"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR - setcontrole" + value
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setosmolateur(state):
    try:
        state = str(state)

        mydb = connect()
        mycursor = mydb.cursor()
        sql = "SELECT `state` FROM `data_osmolateur` ORDER BY `data_osmolateur`.`id`  DESC LIMIT 1"
        mycursor.execute(sql)
        myresult = mycursor.fetchone()[0]

        if myresult != state:

            mydb = connect()
            mycursor = mydb.cursor()
            sql = "INSERT INTO `data_osmolateur`( `state`) VALUES ('" + state + "')"
            mycursor.execute(sql)

            mydb.commit()
            mydb.close()

    except Exception as e:
        message = "SQL - ERREUR - setosmolateur"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setdebit(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        debit = str(value)

        sql = "INSERT INTO `data_reacteur`( `value`) VALUES (" + debit + ")"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR - setdebit"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)
        raise


def mail(m, b):
    try:
        with open('/home/pi/Desktop/www/aqua/config.json') as f:
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
        message = "Mail - ERREUR - mail"
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


def read_file(emplacement):
    exists = os.path.isfile(emplacement)
    if exists:
        file = open(emplacement)
        content = file.read()
        file.close()
        return content
    else:
        return False


def setcompletestate(path, value, error, message, exclude, force_log):
    try:
        path = str(path)
        value = str(value)
        error = str(error)
        message = str(message)
        exclude = str(exclude)
        force_log = str(force_log)

        # on vérifie qu'on est pas déja dans cet état
        mydb = connect()
        mycursor = mydb.cursor()
        mycursor.execute(
            "SELECT count(*) as count FROM `state` WHERE `path` = '" + path + "' AND `value` = '" + value + "'")
        myresult = mycursor.fetchone()[0]
        mydb.close()

        if myresult == 0:
            mydb = connect()
            mycursor = mydb.cursor()
            sql = 'UPDATE `state` set `value`="' + value + '",`error`="' + error + '",`message`="' + message + '", `created_at`=now(), `mail_send`=0, `exclude_check`="' + exclude + '" WHERE `path`="' + path + '"'
            mycursor.execute(sql)
            mydb.commit()
            mydb.close()

            setlog(message)

        if force_log == 1:
            setlog(message)

    except Exception as e:
        message = "SQL - ERREUR - setcompletestate"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def setlog(message):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        message = str(message)

        sql = "INSERT INTO `log`( `message`) VALUES ('" + message + "')"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR - setlog"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise


def notinstatehuit():
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        mycursor.execute(
            "SELECT count(*) as count FROM `state` WHERE `path` = 'osmolateur' AND `value` = 'state_8'")
        myresult = mycursor.fetchone()[0]
        mydb.close()

        if myresult == 0:
            return True
        else:
            return False

    except Exception as e:
        message = "SQL - ERREUR - notinstatehuit"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        print(message)
        mail(message, body)

        raise