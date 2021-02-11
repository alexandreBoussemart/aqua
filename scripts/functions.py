#! /usr/bin/python
# -*-coding:Latin-1 -*
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import RPi.GPIO as GPIO
import smtplib
import mysql.connector
import os, glob
import os.path


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
        mail(message, body)
        setlog(message + str(e))

        raise


def getconfig(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()
        mycursor.execute("SELECT `value` FROM `core_config` WHERE `name` = '" + value + "' LIMIT 1")
        myresult = mycursor.fetchone()[0]
        mydb.close()

        return myresult

    except Exception as e:
        message = "SQL - ERREUR - getConfiguration"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + str(e) + "</p>"
        mail(message, body)
        setlog(message + str(e))

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
        mail(message, body)
        setlog(message + str(e))

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
        mail(message, body)
        setlog(message + str(e))

        raise


def mail(m, b):
    try:
        status = getstatus('mail')
        if status == 0 or status == "0":
            setlogmail(m, b)
            return False

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

        setlogmail(m, b)

        msg.attach(MIMEText(b, 'html'))
        msg['Subject'] = m
        text = msg.as_string()
        server.sendmail(fromaddr, toaddr, text)
        server.quit()

    except Exception as e:
        message = "Mail - ERREUR - mail"
        setlog(message + str(e))
        raise


def on_relay(relay):
    GPIO.setwarnings(False)
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(relay, GPIO.OUT)
    GPIO.output(relay, GPIO.LOW)


def off_relay(relay):
    GPIO.setwarnings(False)
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(relay, GPIO.OUT)
    GPIO.output(relay, GPIO.HIGH)


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
        currentDir = os.path.dirname(os.path.realpath(__file__))
        file = currentDir + '/../state/' + path + '-' + value

        if os.path.isfile(file) == False:
            mydb = connect()
            mycursor = mydb.cursor()
            sql = 'UPDATE `state` set `value`="' + value + '",`error`="' + error + '",`message`="' + message + '", `created_at`=now(), `mail_send`=0, `exclude_check`="' + exclude + '" WHERE `path`="' + path + '"'
            mycursor.execute(sql)
            mydb.commit()
            mydb.close()
            setlog(message)

            # on créer un fichier d'état après avoir supprimer l'ancien
            for filename in glob.glob(currentDir + '/../state/' + path + '-' + "*"):
                os.remove(filename)

            os.mknod(file)

            return True

        if force_log == 1:
            setlog(message)

        return False

    except Exception as e:
        message = "SQL - ERREUR - setcompletestate"
        body = "<p style='color:red;text-transform:uppercase;'>" + message + " - " + str(e) + "</p>"
        mail(message, body)
        setlog(message + str(e))

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
        mail(message, body)
        setlog(message + str(e))

        raise


def setlogmail(sujet, body):
    try:
        mydb = connect()
        mycursor = mydb.cursor()

        sql = "INSERT INTO `log_mail`(`sujet`, `message`) VALUES ('" + sujet + "', '" + body + "')"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except Exception as e:
        message = "SQL - ERREUR - setlog : "
        body = message + str(e)
        setlog(body)

        raise
