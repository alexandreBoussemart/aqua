#!/usr/bin/python
# -*-coding:Latin-1 -*

import mysql.connector


def connect():
    return mysql.connector.connect(
        host="127.0.0.1",
        user="root",
        passwd="",
        database="aqua-web"
    )


def setdebit(value):
    try:
        mydb = connect()
        mycursor = mydb.cursor()

        sql = "INSERT INTO `reacteur`( `value`) VALUES ("+value+")"
        mycursor.execute(sql)

        mydb.commit()
        mydb.close()

    except:
        print()



setdebit('2600')