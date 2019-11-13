<?php

require_once __DIR__ .'/../vendor/autoload.php';
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
    $sql = 'INSERT INTO `temperature` ( `value`) VALUES ("' . strval($temp) . '")';
    $link->query($sql);
}

/**
 * @param $link
 *
 * @return false|string
 */
function readFileTemperature($link) {
    // on récupère le contenu du fichier
    if(file_exists(THERMOMETER_SENSOR_PATH)) {
        $thermometer = fopen(THERMOMETER_SENSOR_PATH, "r");
        $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH));
        fclose($thermometer);
    } else {
        setState($link, 'temperature','state_1',true, "Cron temperature - ERREUR - Le fichier : ".THERMOMETER_SENSOR_PATH." n'existe pas.");
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
     preg_match("/t=(.+)/", $lines[1], $matches);

    if (strpos($lines[0],'NO') !== false || $matches[1] == "85000") {
        return False;
    }

    $temperature = floatval($matches[1]);
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

/**
 * @param $link
 * @param $path
 * @param $value
 * @param $error
 * @param $message
 */
function setState($link, $path, $value, $error, $message) {
    //on vérifie qu'on est pas déja dans cet état
    $sql = "SELECT count(*) as count FROM `state` WHERE `path` = '".$path."' AND `value` = '".$value."'";
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if($result['count'] == "0" || $result['count'] == 0) {
        deleteState($link, $path);
        $sql = 'INSERT INTO `state`( `path`,`value`,`error`,`message`) VALUES ("' . $path . '","' . $value . '","' . $error . '","' . $message . '")';
        $link->query($sql);
    }

}

/**
 * @param $link
 * @param $path
 */
function deleteState($link, $path) {
    $sql = "DELETE FROM `state` WHERE `path`='" . $path . "'";
    $link->query($sql);
}




