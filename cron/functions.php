<?php

require_once '../vendor/autoload.php';
require 'bdd.php';

/**
 * @param $data
 * @param $transport
 * @param $subject
 * @param $content
 *
 * @return int
 */
function sendMail($data, $transport, $subject, $content) {
    $mailer = new Swift_Mailer($transport);
    $message = new Swift_Message($subject);
    $message
        ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
        ->setTo([$data['mail_to']])
        ->setSubject($subject)
        ->setBody($content, 'text/html');

    return $mailer->send($message);
}

/**
 * @param $link
 * @param $name
 *
 * @return bool
 */
function getConfig($link, $name) {
    $result = true;

    $sql = "SELECT `value` FROM `config` WHERE `name` = '".$name."'";
    $controle = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($controle);

    if($row && $row['value'] === "0") {
        $result = false;
    }

    return $result;
}

/**
 * @param $link
 * @param $temp
 */
function insertTemperature($link, $temp) {
    $sql = "INSERT INTO `temperature`( `value`) VALUES (" + $temp + ")";
    $link->query($sql);
}

/**
 * @param $data
 * @param $transport
 * @return bool|string
 */
function readFileTemperature($data, $transport) {
    // on récupère le contenu du fichier
    if(file_exists(THERMOMETER_SENSOR_PATH)) {
        $thermometer = fopen(THERMOMETER_SENSOR_PATH, "r");
        $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH));
        fclose($thermometer);
    } else {
        sendMail($data, $transport, "Cron temperature - ERREUR", "Le fichier : ".THERMOMETER_SENSOR_PATH." n'existe pas.");
        exit;
    }

    return $content;
}

/**
 * @param $content
 *
 * @return bool|false|float|int
 */
function readTemperature($content) {
    $lines = preg_split("/\n/", $content);
    $temperature = preg_match("/t=(.+)/", $lines[1], $matches);

    if (strpos($lines[0],'NO') !== false || $temperature == "85000") {
        return False;
    }

    $temperature = floatval($temperature);
    $temperature = $temperature / 1000;

    return $temperature;
}

/**
 * @param $link
 * @param $value
 */
function setControle($link, $value) {
    deleteControle($link, $value);
    $sql = "INSERT INTO `controle`( `value`) VALUES ('" . $value . "')";
    $link->query($sql);
}

/**
 * @param $link
 * @param $value
 */
function deleteControle($link, $value) {
    $sql = "DELETE FROM `controle` WHERE value='" . $value . "'";
    $link->query($sql);
}




