#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Fonctions communes aux scripts GPIO : accès BDD (requêtes paramétrées), mails, relais, fichiers d'état."""
import glob
import json
import os
import smtplib
from contextlib import closing
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

import mysql.connector
import RPi.GPIO as GPIO

# Racine du projet (scripts/..) : fonctionne sur le Pi comme dans Docker
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..'))
CONFIG_FILE = os.path.join(BASE_DIR, 'config.json')
STATE_DIR = os.path.join(BASE_DIR, 'state')


def load_config():
    with open(CONFIG_FILE) as f:
        return json.load(f)


def connect():
    db = load_config()["database"][0]
    return mysql.connector.connect(
        host=db["host"],
        user=db["user"],
        passwd=db["passwd"],
        database=db["database"]
    )


def _error_body(message, e):
    return "<p style='color:red;text-transform:uppercase;'>" + message + " - " + str(e) + "</p>"


def query_one(sql, params=()):
    """SELECT -> première colonne de la première ligne (ou None)."""
    with closing(connect()) as db, closing(db.cursor()) as cursor:
        cursor.execute(sql, params)
        row = cursor.fetchone()
        return row[0] if row else None


def execute(sql, params=()):
    """INSERT / UPDATE / DELETE avec commit."""
    with closing(connect()) as db, closing(db.cursor()) as cursor:
        cursor.execute(sql, params)
        db.commit()


def _guarded(label, func, *args, notify=True):
    """Exécute func ; en cas d'erreur : log + mail (si notify) puis relance l'exception."""
    try:
        return func(*args)
    except Exception as e:
        message = "SQL - ERREUR - " + label
        if notify:
            mail(message, _error_body(message, e))
        setlog(message + " " + str(e))
        raise


def getstatus(value):
    return _guarded("getstatus", query_one,
                    "SELECT `value` FROM `core_config_status` WHERE `name` = %s LIMIT 1", (value,))


def getconfig(value):
    return _guarded("getConfiguration", query_one,
                    "SELECT `value` FROM `core_config_value` WHERE `name` = %s LIMIT 1", (value,))


def setcontrole(value):
    _guarded("setcontrole " + str(value), execute,
             "UPDATE `last_activity` SET `created_at` = now() WHERE `value` = %s", (str(value),))


def setdebit(value):
    _guarded("setdebit", execute, "INSERT INTO `data_reacteur` (`value`) VALUES (%s)", (value,))


def mail(subject, body):
    try:
        status = getstatus('mail')
        if str(status) == "0":
            setlogmail(subject, body)
            return False

        data = load_config()
        gmail = data["gmail"][0]
        msg = MIMEMultipart()
        msg['From'] = gmail["mail"]
        msg['To'] = data["mail_to"]
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'html'))

        setlogmail(subject, body)

        with smtplib.SMTP(gmail["server"], gmail["port"], timeout=30) as server:
            server.starttls()
            server.login(gmail["mail"], gmail["password"])
            server.sendmail(gmail["mail"], data["mail_to"], msg.as_string())

        return True

    except Exception as e:
        setlog("Mail - ERREUR - mail " + str(e))
        raise


def _set_relay(relay, level):
    GPIO.setwarnings(False)
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(relay, GPIO.OUT)
    GPIO.output(relay, level)


def on_relay(relay):
    _set_relay(relay, GPIO.LOW)


def off_relay(relay):
    _set_relay(relay, GPIO.HIGH)


def read_file(emplacement):
    if not os.path.isfile(emplacement):
        return False
    with open(emplacement) as f:
        return f.read()


def setcompletestate(path, value, error, message, exclude, force_log):
    """Met à jour l'état d'un équipement s'il a changé (fichier state/<path>-<value> comme cache)."""
    try:
        path, value, message = str(path), str(value), str(message)
        state_file = os.path.join(STATE_DIR, path + '-' + value)

        if not os.path.isfile(state_file):
            execute(
                "UPDATE `core_config_state` SET `value` = %s, `error` = %s, `message` = %s, `created_at` = now(), "
                "`mail_send` = 0, `exclude_check` = %s WHERE `path` = %s",
                (value, int(error), message, int(exclude), path)
            )
            setlog(message)

            # on crée le fichier d'état après avoir supprimé l'ancien
            for filename in glob.glob(os.path.join(STATE_DIR, path + '-*')):
                os.remove(filename)
            open(state_file, 'w').close()

            return True

        if str(force_log) == '1':
            setlog(message)

        return False

    except Exception as e:
        message = "SQL - ERREUR - setcompletestate"
        mail(message, _error_body(message, e))
        setlog(message + " " + str(e))
        raise


def setlog(message):
    try:
        execute("INSERT INTO `log` (`message`) VALUES (%s)", (str(message),))
    except Exception as e:
        message = "SQL - ERREUR - setlog"
        mail(message, _error_body(message, e))
        raise


def setlogmail(sujet, body):
    try:
        execute("INSERT INTO `log_mail` (`sujet`, `message`) VALUES (%s, %s)", (str(sujet), str(body)))
    except Exception as e:
        setlog("SQL - ERREUR - setlogmail : " + str(e))
        raise


def _osmolateur_in_state(state):
    return _guarded("osmolateur " + state, query_one,
                    "SELECT count(*) FROM `core_config_state` WHERE `path` = 'osmolateur' AND `value` = %s",
                    (state,)) > 0


def notinstatehuit():
    return not _osmolateur_in_state('state_8')


def instatetrois():
    return _osmolateur_in_state('state_3')
