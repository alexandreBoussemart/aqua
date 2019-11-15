<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require 'bdd.php';

$array_verif = [
    'controle_bailling',
    'controle_ecumeur',
    'controle_osmolateur',
    'controle_reacteur',
    'controle_temperature'
];

$message_body = [
    'controle_bailling'    => 'Cron - Erreur script bailling',
    'controle_ecumeur'     => 'Cron - Erreur script écumeur',
    'controle_osmolateur'  => 'Cron - Erreur script osmolateur',
    'controle_reacteur'    => 'Cron - Erreur script réacteur',
    'controle_temperature' => 'Cron - Erreur script température'
];

$rappel = [
    "Mon" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color: #9B59B6">Bactérie</p><p style="color:#1ABB9C ">Algue</p>',
    "Tue" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
    "Wed" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color:#1ABB9C ">Algue</p>',
    "Thu" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
    "Fri" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color:#1ABB9C ">Algue</p>',
    "Sat" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
    "Sun" => '<p style="color: #3a87ad;">Nourriture congelée</p>'
];

/**
 * @param $data
 * @param $transport
 * @param $subject
 * @param $content
 *
 * @return int
 */
function sendMail($data, $transport, $subject, $content)
{
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
function getStatus($link, $name)
{
    $result = true;

    $sql = "SELECT `value` FROM `status` WHERE `name` = '" . $name . "'";
    $controle = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($controle);

    if ($row && $row['value'] === "0") {
        $result = false;
    }

    return $result;
}

/**
 * @param $link
 * @param $temp
 */
function insertTemperature($link, $temp)
{
    $sql = 'INSERT INTO `temperature` ( `value`) VALUES ("' . strval($temp) . '")';
    $link->query($sql);
}

/**
 * @param $link
 *
 * @return false|string
 */
function readFileTemperature($link)
{
    // on récupère le contenu du fichier
    if (file_exists(THERMOMETER_SENSOR_PATH)) {
        $thermometer = fopen(THERMOMETER_SENSOR_PATH, "r");
        $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH));
        fclose($thermometer);
    } else {
        setState($link, 'temperature', 'state_1', true,
            "Cron temperature - ERREUR - Le fichier : " . THERMOMETER_SENSOR_PATH . " n'existe pas.");
        exit;
    }

    return $content;
}

/**
 * @param $content
 *
 * @return bool|false|float|int
 */
function readTemperature($content)
{
    $lines = preg_split("/\n/", $content);
    preg_match("/t=(.+)/", $lines[1], $matches);

    if (strpos($lines[0], 'NO') !== false || $matches[1] == "85000") {
        return false;
    }

    $temperature = floatval($matches[1]);
    $temperature = $temperature / 1000;

    return $temperature;
}

/**
 * @param $link
 * @param $value
 */
function setControle($link, $value)
{
    $sql = "UPDATE `controle` set `value`='" . $value . "', `created_at`=now() WHERE `value`='" . $value . "'";
    $link->query($sql);
}

/**
 * @param      $link
 * @param      $path
 * @param      $value
 * @param      $error
 * @param      $message
 * @param int  $exclude
 * @param bool $force_log
 */
function setState($link, $path, $value, $error, $message, $exclude = 0, $force_log = false)
{
    //on vérifie qu'on est pas déja dans cet état
    $sql = "SELECT count(*) as count FROM `state` WHERE `path` = '" . $path . "' AND `value` = '" . $value . "'";
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $sql = "UPDATE `state` set `value`='" . $value . "',`error`='" . $error . "',`message`='" . $message . "', `created_at`=now(), `mail_send`=0, `exclude_check`='" . $exclude . "' WHERE `path`='" . $path . "'";
        $link->query($sql);

        // met ligne dans table log
        setLog($link, $message);
    }

    if ($force_log) {
        setLog($link, $message);
    }
}

/**
 * @param $link
 * @param $message
 */
function setLog($link, $message)
{
    // met ligne dans table log
    $sql = 'INSERT INTO `log`(`message`) VALUES ("' . $message . '")';
    $link->query($sql);
}


