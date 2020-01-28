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
    'controle_bailling' => 'Cron - Erreur script bailling',
    'controle_ecumeur' => 'Cron - Erreur script écumeur',
    'controle_osmolateur' => 'Cron - Erreur script osmolateur',
    'controle_reacteur' => 'Cron - Erreur script réacteur',
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
    try {
        $result = true;

        $sql = "SELECT `value` FROM `status` WHERE `name` = '" . $name . "'";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row && $row['value'] === "0") {
            $result = false;
        }

        return $result;
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $temp
 */
function insertTemperature($link, $temp)
{
    try {
        $sql = 'INSERT INTO `temperature` ( `value`) VALUES ("' . strval($temp) . '")';
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 *
 * @return false|string
 */
function readFileTemperature($link)
{
    try {
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
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $content
 * @return bool|float|int
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
    try {
        $sql = "UPDATE `controle` set `value`='" . $value . "', `created_at`=now() WHERE `value`='" . $value . "'";
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param      $link
 * @param      $path
 * @param      $value
 * @param      $error
 * @param      $message
 * @param int $exclude
 * @param bool $force_log
 */
function setState($link, $path, $value, $error, $message, $exclude = 0, $force_log = false)
{
    try {
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
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
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

/**
 * @param $key
 *
 * @return mixed
 */
function getLabel($key)
{
    $array = [
        '' => "",
        'off' => "Off",
        'ok' => "Niveau d'eau OK",
        "pump_on" => "En cours de remplissage",
        "to_low" => "Niveau d'eau bas",
        "to_high" => "Niveau d'eau haut",
        "off_rappel" => "RAPPEL - Off",
        "to_low_rappel" => "RAPPEL - Niveau d'eau bas",
        "pump_on_20" => "Pompe allumée plus de 20 secondes",
        "pump_on_20_rappel" => "RAPPEL - Pompe allumée plus de 20 secondes",
        "to_high_rappel" => "RAPPEL - Niveau d'eau haut",
        "error" => "Erreur"
    ];

    return $array[$key];
}

/**
 * @param $date
 *
 * @return string
 * @throws Exception
 */
function getFormattedDate($date)
{
    $format = new DateTime($date);

    return $format->format('d/m/Y à H:i:s');
}

/**
 * @param $link
 * @param $data
 * @param $code
 */
function setStatus($link, $data, $code)
{
    try {
        if (isset($data)) {
            $value = 1;
        } else {
            $value = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value . "' WHERE `name` = '$code'";
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @return bool
 * @throws Exception
 */
function isOn()
{
    $date = new DateTime();
    $now = $date->format('Y-m-d H:i:s');

    if (
        ($now >= $date->format('Y-m-d 22:30:00') && $now < $date->format('Y-m-d 23:30:00')) ||
        ($now >= $date->format('Y-m-d 23:40:00') || $now < $date->format('Y-m-d 00:40:00')) ||
        ($now >= $date->format('Y-m-d 00:50:00') && $now < $date->format('Y-m-d 01:50:00')) ||
        ($now >= $date->format('Y-m-d 02:00:00') && $now < $date->format('Y-m-d 03:00:00')) ||
        ($now >= $date->format('Y-m-d 03:10:00') && $now < $date->format('Y-m-d 04:10:00')) ||
        ($now >= $date->format('Y-m-d 04:20:00') && $now < $date->format('Y-m-d 05:20:00')) ||
        ($now >= $date->format('Y-m-d 05:30:00') && $now < $date->format('Y-m-d 06:30:00')) ||
        ($now >= $date->format('Y-m-d 06:40:00') && $now < $date->format('Y-m-d 07:40:00')) ||
        ($now >= $date->format('Y-m-d 07:50:00') && $now < $date->format('Y-m-d 08:50:00')) ||
        ($now >= $date->format('Y-m-d 09:00:00') && $now < $date->format('Y-m-d 10:00:00'))
    ) {
        return true;
    }

    return false;
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @return bool
 */
function checkChangementEau($data, $transport, $link)
{
    $periode = '-15 days';
    $date = new DateTime();
    $date->modify($periode);
    $date = $date->format('Y-m-d H:i:s');

    $sql = "SELECT count(*) as count FROM `data_changement_eau` WHERE `created_at` > '" . $date . "'";
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $body = "<p style=\"color: red;\">Pas de changement d'eau depuis plus de 15 jours !</p>";
        sendMail($data, $transport, "Rappel - faire un changement d'eau", $body);

        return false;
    }

    return true;
}

/**
 * @param $link
 */
function clear($link)
{
    try {
        $date = new DateTime();
        $periode = '-30 days';
        $date->modify($periode);
        $limit = $date->format('Y-m-d H:i:s');

        $sql = "DELETE FROM `log` WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "DELETE FROM `data_osmolateur` WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "DELETE FROM `data_reacteur` WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "DELETE FROM `temperature` WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}