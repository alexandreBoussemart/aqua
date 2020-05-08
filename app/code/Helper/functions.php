<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require 'bdd.php';

$array_verif = [
    'controle_bailling',
    'controle_ecumeur',
    'controle_osmolateur',
    'controle_reacteur',
    'controle_temperature'
];

$message_body = [
    'controle_bailling' => 'Cron - ERREUR - script bailling',
    'controle_ecumeur' => 'Cron - ERREUR - script écumeur',
    'controle_osmolateur' => 'Cron - ERREUR - script osmolateur',
    'controle_reacteur' => 'Cron - ERREUR - script réacteur',
    'controle_temperature' => 'Cron - ERREUR - script température'
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
 * @param null $link
 * @param bool $force
 * @return int
 */
function sendMail($data, $transport, $subject, $content, $link = null, $force = false)
{
    //check si la cron est activé
    if (!$force && !getStatus($link, 'mail')) {
        setLogMail($link, $subject, $content);
        exit;
    }

    $mailer = new Swift_Mailer($transport);
    $message = new Swift_Message($subject);
    $message
        ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
        ->setTo([$data['mail_to']])
        ->setSubject($subject)
        ->setBody($content, 'text/html');

    $result = $mailer->send($message);

    setLogMail($link, $subject, $content);

    return $result;
}

/**
 * @param $link
 * @param $name
 * @return bool
 */
function getStatus($link, $name)
{
    try {
        return file_exists(__DIR__ . "/../../../config/" . $name);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $temp
 * @param string $table
 */
function insertTemperature($link, $temp, $table = "`data_temperature`")
{
    try {
        $sql = '# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO ' . $table . ' ( `value`) 
                VALUES ("' . strval($temp) . '")';
        logInFile($link, "sql.log", $sql);
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @return bool|string
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
 * @param $link
 * @return false|string
 */
function readFileTemperatureBoitier($link)
{
    try {

        // on récupère le contenu du fichier
        if (file_exists(THERMOMETER_SENSOR_PATH_BOITIER)) {
            $thermometer = fopen(THERMOMETER_SENSOR_PATH_BOITIER, "r");
            $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH_BOITIER));
            fclose($thermometer);
        } else {
            setLog($link, "ERREUR - Le fichier : " . THERMOMETER_SENSOR_PATH_BOITIER . " n'existe pas.");
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
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE `last_activity` set `value`='" . $value . "', `created_at`=now() 
                WHERE `value`='" . $value . "'";
        logInFile($link, "sql.log", $sql);
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $path
 * @param $value
 * @param $error
 * @param $message
 * @param int $exclude
 * @param bool $force_log
 * @return bool
 */
function setState($link, $path, $value, $error, $message, $exclude = 0, $force_log = false)
{
    try {
        //on vérifie qu'on est pas déja dans cet état
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT count(*) as count 
                FROM `state` 
                WHERE `path` = '" . $path . "' 
                AND `value` = '" . $value . "'";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $result = mysqli_fetch_assoc($request);

        if ($result['count'] == "0" || $result['count'] == 0) {
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                    UPDATE `state` set `value`='" . $value . "',`error`='" . $error . "',`message`='" . $message . "', `created_at`=now(), `mail_send`=0, `exclude_check`='" . $exclude . "' 
                    WHERE `path`='" . $path . "'";
            logInFile($link, "sql.log", $sql);
            $link->query($sql);

            // met ligne dans table log
            setLog($link, $message);

            return true;
        }

        if ($force_log) {
            setLog($link, $message);
        }

        return false;

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
    $message = str_replace("'", "\'", $message);
    // met ligne dans table log
    $sql = '# noinspection SqlNoDataSourceInspectionForFile 
            INSERT INTO `log` (`message`) 
            VALUES ("' . $message . '")';
    logInFile($link, "sql.log", $sql);
    $link->query($sql);
}

/**
 * @param $link
 * @param $sujet
 * @param $message
 */
function setLogMail($link, $sujet, $message)
{
    $message = str_replace('"', "'", $message);
    $message = str_replace("'", "\'", $message);
    $sujet = str_replace("'", "\'", $sujet);
    // met ligne dans table log
    $sql = '# noinspection SqlNoDataSourceInspectionForFile 
            INSERT INTO `log_mail` (`sujet`, `message`) 
            VALUES ("' . $sujet . '","' . $message . '")';
    logInFile($link, "sql.log", $sql);
    $link->query($sql);
}

/**
 * @param $key
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
 * @return string
 * @throws Exception
 */
function getFormattedDate($date)
{
    $format = new DateTime($date);

    return $format->format('d/m/Y à H:i:s');
}

/**
 * @param $date
 * @return string
 * @throws Exception
 */
function getFormattedDateWithouH($date)
{
    $format = new DateTime($date);

    return $format->format('d/m/Y');
}

/**
 * @param $date1
 * @param $date2
 * @return float|int
 * @throws Exception
 */
function getNumberDaysBetweenDate($date1, $date2)
{
    $dateStart = new DateTime($date1);
    $dateEnd = new DateTime($date2);

    $x1 = days($dateStart);
    $x2 = days($dateEnd);

    if ($x1 && $x2) {
        return abs($x1 - $x2);
    }
}

/**
 * @param $x
 * @return bool|float|int
 */
function days($x)
{
    if (get_class($x) != 'DateTime') {
        return false;
    }

    $y = $x->format('Y') - 1;
    $days = $y * 365;
    $z = (int)($y / 4);
    $days += $z;
    $z = (int)($y / 100);
    $days -= $z;
    $z = (int)($y / 400);
    $days += $z;
    $days += $x->format('z');

    return $days;
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
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE `status` 
                SET `value`='" . $value . "' 
                WHERE `name` = '$code'";
        logInFile($link, "sql.log", $sql);
        $link->query($sql);

        // on set le status en fichier
        if ($value == 1) {
            exec("touch " . __DIR__ . "/../../../config/" . $code);
        } else {
            exec("rm " . __DIR__ . "/../../../config/" . $code);
        }

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @param $data
 * @param $code
 */
function setConfig($link, $data, $code)
{
    try {
        if (isset($data)) {
            if ($data == "on") {
                $value = 1;
            } else {
                $value = $data;
            }
        } else {
            $value = 0;
        }
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE `core_config` 
                SET `value`='" . $value . "' 
                WHERE `name` = '$code'";
        logInFile($link, "sql.log", $sql);
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @return bool
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
 * @param $link
 */
function clear($link)
{
    try {
        $date = new DateTime();
        $periode = '-30 days';
        $date->modify($periode);
        $limit = $date->format('Y-m-d H:i:s');

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM `log` 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM `data_osmolateur` 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM `data_reacteur` 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM `data_temperature` 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM `log_mail` 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @return bool
 */
function getStatusVentilateur($link)
{
    try {
        $result = false;
        $temperature = getConfig($link, "config_temperature_declenchement");

        if ($temperature <= intval($temperature)) {
            $result = true;
        }

        return $result;
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $name
 * @return bool
 */
function getConfig($link, $name)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM `core_config` 
                WHERE `name` = '" . $name . "'";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row && intval($row['value']) == 1) {
            $result = true;
        } elseif ($row && intval($row['value']) == 0) {
            $result = false;
        } else {
            $result = $row['value'];
        }

        return $result;
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $data
 * @param $transport
 */
function envoyerMail($link, $data, $transport)
{
    try {
        // on fait le premier mail
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM `state` 
            WHERE `exclude_check` LIKE 0 
            AND `mail_send` LIKE 0";
        logInFile($link, "sql.log", $sql);
        $mails = mysqli_query($link, $sql);
        $rows = $mails->fetch_all();
        foreach ($rows as $row) {
            $id = $row[0];
            $message = $row[5];
            $error = $row[4];

            if ($error == 1 || $error == "1") {
                $body = "<p style='color: red; text-transform: uppercase'>" . $message . "</p>";
            } else {
                $body = "<p style='color: green;'>" . $message . "</p>";
            }

            // on envoie le mail
            try {
                sendMail($data, $transport, $message, $body, $link);
            } catch (Exception $e) {
                setLog($link, $e->getMessage());
            }

            // on set comme quoi le mail a été envoyé et on renit la date
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE `state` 
                SET `mail_send`=1,`created_at`=now() 
                WHERE `id` LIKE " . $id;
            logInFile($link, "sql.log", $sql);
            $link->query($sql);
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $data
 * @param $transport
 * @param $tempsMailRappel
 */
function envoyerMailRappel($link, $data, $transport, $tempsMailRappel)
{
    try {
        //on fait le mail de rappel et renit la date a now
        $date = new DateTime();
        $date->modify($tempsMailRappel);
        $date = "'" . $date->format('Y-m-d H:i:00') . "'";

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM `state` 
            WHERE `exclude_check` LIKE 0 
            AND `error` LIKE 1 
            AND `created_at` < " . $date;
        logInFile($link, "sql.log", $sql);
        $mails = mysqli_query($link, $sql);
        $rows = $mails->fetch_all();

        foreach ($rows as $row) {
            $id = $row[0];
            $message = $row[5];

            $message = str_replace('ERREUR', 'RAPPEL ERREUR', $message);
            $body = "<p style='color: red; text-transform: uppercase'>" . $message . "</p>";

            // on envoie le mail
            try {
                sendMail($data, $transport, $message, $body, $link);
                setLog($link, $message);
            } catch (Exception $e) {
                setLog($link, $e->getMessage());
            }

            // on renit la date
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE `state` 
                SET `created_at`=now() 
                WHERE `id` LIKE " . $id;
            logInFile($link, "sql.log", $sql);
            $link->query($sql);
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $data
 * @param $transport
 */
function checkDisableSendMail($link, $data, $transport)
{
    try {
        //on fait le mail de rappel et renit la date a now
        $date = $date2 = new DateTime();
        $date->modify("-2 minutes");
        $date = "'" . $date->format('Y-m-d H:i:00') . "'";

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count
            FROM `log_mail` 
            WHERE `created_at` > " . $date;
        logInFile($link, "sql.log", $sql);
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);

        $count = $row['count'];

        $status = getStatus($link, 'mail');

        if ($count && intval($count) > 10 && $status) {
            setStatus($link, null, 'mail');

            $message = "Spam Mail, " . $count . " mails envoyé depuis " .
                getFormattedDate($date2->format('Y-m-d H:i:s')) .
                ". Envoie mail désactive.";
            $body = "<p style=\"color: red;\">" . $message . "</p>";
            sendMail($data, $transport, "Erreur - Spam mail", $body, $link, true);
            setLog($link, $message);
        }

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $data
 * @param $transport
 */
function envoyerMail8h($link, $data, $transport)
{
    try {
        //controle 8h
        $date = new DateTime();
        $current = $date->format('Y-m-d H:i:00');
        $huit = $date->format('Y-m-d 08:00:00');

        if ($current == $huit) {
            $content = "<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>";
            $content .= "<p>Dernier débit enregistré : " . getLastData($link, "data_reacteur", " l/min") . "</p>";
            $content .= "<p>Dernière température enregistrée : " . getLastData($link, "data_temperature", " °C") . "</p>";

            $checks = allCheckLastTimeCheck($data, $transport, $link, false);
            $checks = array_filter($checks);
            if (count($checks) > 0) {
                $content .= "<p>RAPPEL À FAIRE :</p>";
                $content .= "<ul>";
                foreach ($checks as $check) {
                    $content .= "<li>" . $check . "</li>";
                }
                $content .= "</ul>";
            }

            $message = "Cron - contrôle 8h - OK";

            // on envoie le mail
            sendMail($data, $transport, $message, $content, $link);
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $tempsMaxPompeOsmolateur
 * @return bool
 */
function isRunOver20seconds($link, $tempsMaxPompeOsmolateur)
{
    try {
        // si c'est le state 3 et qu'il a moins de 20 secondes
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM `state` 
            WHERE `path` LIKE 'osmolateur' 
            AND (`value` LIKE 'state_3' OR `value` LIKE 'state_8')";
        logInFile($link, "sql.log", $sql);
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row) {
            $maxDate = new DateTime();
            $maxDate->modify($tempsMaxPompeOsmolateur);
            $dateState = new DateTime($row["created_at"]);

            if ($row["value"] == 'state_8') {
                //si en rappel
                return true;
            } elseif ($dateState > $maxDate) {
                //si moins de 20 secondes
                return false;
            } else {
                // c'est que c'est plus de 20 secondes, donc on met en erreur
                $message = "Osmolateur - ERREUR - pompe allumée plus de 20 secondes";
                setState($link, 'osmolateur', 'state_8', 1, $message);

                return true;
            }
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return true;
}

/**
 * @param $link
 * @param $data
 * @param $type
 */
function setParam($link, $data, $type)
{
    try {
        $data = str_replace(',', '.', $data);
        if (isset($data) && is_numeric($data)) {
            $sql = '# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO `data_parametres_eau` (`type`, `value`) 
                VALUES ("' . $type . '", "' . strval($data) . '")';
            logInFile($link, "sql.log", $sql);
            $link->query($sql);
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @param array $param
 * @param string $message
 * @param string $subject
 * @return bool
 */
function checkLastTimeCheck($data, $transport, $link, array $param, $message, $subject, $config_path, $sendMail)
{
    try {
        $table = $param['table'];
        $type = $param['type'];

        $periode = '-' . getConfig($link, $config_path) . ' days';
        $date = new DateTime();
        $date->modify($periode);
        $date = $date->format('Y-m-d H:i:s');

        $AND = '';
        if ($type != '') {
            $AND = " AND `type` LIKE '" . $type . "'";
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count
            FROM `" . $table . "` 
            WHERE `created_at` > '" . $date . "'"
            . $AND;
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $result = mysqli_fetch_assoc($request);

        if ($result['count'] == "0" || $result['count'] == 0) {
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` as created_at
                FROM `" . $table . "` 
                WHERE `id` > 0"
                . $AND
                . " ORDER BY `id` DESC LIMIT 1";
            logInFile($link, "sql.log", $sql);
            $request = mysqli_query($link, $sql);
            $result = mysqli_fetch_assoc($request);

            $lastDate = $result["created_at"];

            $days = getNumberDaysBetweenDate($lastDate, date("Y-m-d H:i:s"));
            $message = str_replace('XX', $days, $message);

            $body = "<p style=\"color: red;\">" . $message . "</p>";
            if ($sendMail) {
                sendMail($data, $transport, $subject, $body, $link);
            }
            setLog($link, $message);

            return $message;
        }

        return null;

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return true;
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @param bool $sendMail
 * @return array
 */
function allCheckLastTimeCheck($data, $transport, $link, $sendMail = true)
{
    $result = [];

    //si pas de changement d'eau depuis plus de 15 jours on envoie un mail de rappel
    $message = "Pas de changement d'eau depuis XX jours !";
    $subject = "Rappel - faire un changement d'eau";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_changement_eau', 'type' => ''], $message, $subject, "check_changement_eau", $sendMail);

    //si pas nettoyé le reacteur depuis plus de 15 jours on envoie un mail de rappel
    $message = "Le réacteur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer le reacteur";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_reacteur', 'type' => ''], $message, $subject, "check_clean_reacteur", $sendMail);

    //si pas nettoyé le écumeur depuis plus de 30 jours on envoie un mail de rappel
    $message = "L'écumeur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer l'écumeur";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_ecumeur', 'type' => ''], $message, $subject, "check_clean_ecumeur", $sendMail);

    //si pas nettoyé les pompes depuis plus de 90 jours on envoie un mail de rappel
    $message = "Les pompes n'ont pas été nettoyé depuis depuis XX jours !";
    $subject = "Rappel - nettoyer les pompes";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_pompes', 'type' => ''], $message, $subject, "check_clean_pompes", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Ca depuis XX jours !";
    $subject = "Rappel - faire une mesure du Ca";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'ca'], $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Mg depuis XX jours !";
    $subject = "Rappel - faire une mesure du Mg";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'mg'], $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Kh depuis XX jours !";
    $subject = "Rappel - faire une mesure du Kh";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'kh'], $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de la densité depuis XX jours !";
    $subject = "Rappel - faire une mesure de la densité";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'densite'], $message, $subject, "check_analyse_eau", $sendMail);

    return $result;
}

/**
 * @param $link
 * @param $table
 * @param $suffix
 * @return string
 */
function getLastData($link, $table, $suffix)
{
    try {
        // dernière temperature
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `value`,`created_at` 
            FROM `" . $table . "` 
            ORDER BY `id` DESC 
            LIMIT 1";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        $last_data = round($row['value'], 2);

        if (!isset($last_data)) {
            $last_data = 0;
        }

        return $last_data . $suffix;

    } catch (Exception $e) {
        setLog($link, $e->getMessage());

        return '';
    }
}

/**
 * @param $link
 * @param $type
 * @return bool
 */
function clean($link, $type)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO `data_clean_" . $type . "` (`id`, `created_at`) 
                VALUES (NULL, CURRENT_TIMESTAMP);";
        logInFile($link, "sql.log", $sql);
        $link->query($sql);

        return true;
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());

        return false;
    }
}

/**
 * @param $link
 * @param $type
 * @return string|null
 * @throws Exception
 */
function getDateLastClean($link, $type)
{
    $sql = "# noinspection SqlNoDataSourceInspectionForFile
            SELECT `created_at` 
            FROM `data_clean_" . $type . "` 
            ORDER BY `id` DESC 
            LIMIT 1";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($request);
    $created_at = $row['created_at'];

    if ($created_at) {
        return getFormattedDateWithouH($created_at);
    }

    return null;
}

/**
 * @param $link
 * @param $file
 * @param $message
 */
function logInFile($link, $file, $message)
{
    if (getStatus($link, 'log_in_files') == true) {
        $file = __DIR__ . "/../../../var/log/" . $file;
        $fp = fopen($file, "a+");
        fwrite($fp, date("Y-m-d H:i:s") . " : " . $message . PHP_EOL);
        fwrite($fp, "------------------------------------" . PHP_EOL);
        fclose($fp);
    }
}

/**
 * @param $type
 * @param $message
 */
function setMessage($type, $message)
{
    session_start();
    $data[$type][] = $message;
    $_SESSION = $data;
    session_write_close();
}

/**
 * @param $link
 * @param $type
 * @return string
 */
function getLastParam($link, $type)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM `data_parametres_eau` 
                WHERE `type` = '" . $type . "' 
                ORDER BY `id` DESC 
                LIMIT 1";
        logInFile($link, "sql.log", $sql);

        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);

        switch ($type) {
            case 'kh':
                $label = 'dkh';
                break;
            case 'mg':
            case 'ca':
                $label = 'mg/l';
                break;
            case 'densite':
                $label = '';
        }

        return $row["value"] . " " . $label . " le " . getFormattedDateWithouH($row["created_at"]);

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @return string
 */
function getLastChangementEau($link)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM `data_changement_eau` 
                ORDER BY `id` DESC 
                LIMIT 1";
        logInFile($link, "sql.log", $sql);

        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);

        return $row["value"] . " litres le " . getFormattedDateWithouH($row["created_at"]);

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @return string
 */
function getOlderData($link)
{
    try {
        $dates = [];

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM `log` 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM `data_osmolateur` 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM `data_reacteur` 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM `data_temperature` 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM `log_mail` 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $oldDate = new DateTime();

        foreach ($dates as $date) {
            if ($oldDate > $date) {
                $oldDate = $date;
            }
        }

        return getFormattedDateWithouH($oldDate->format('Y-m-d H:i:s'));

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}