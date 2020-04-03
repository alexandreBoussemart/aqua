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
    if (!$force && $link && !getStatus($link, 'mail')) {
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
        $result = true;

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM `status` 
                WHERE `name` = '" . $name . "'";
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
        $sql = '# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO `data_temperature` ( `value`) 
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
 */
function getFormattedDate($date)
{
    $format = new DateTime($date);

    return $format->format('d/m/Y à H:i:s');
}

/**
 * @param $date
 * @return string
 */
function getFormattedDateWithouH($date)
{
    $format = new DateTime($date);

    return $format->format('d/m/Y');
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
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
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
    $message = "Pas de changement d'eau depuis plus de 15 jours !";

    $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count 
            FROM `data_changement_eau` 
            WHERE `created_at` > '" . $date . "'";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $body = "<p style=\"color: red;\">" . $message . "</p>";
        sendMail($data, $transport, "Rappel - faire un changement d'eau", $body, $link);
        setLog($link, $message);

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
            $result = true;
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
 */
function envoyerMailRappel($link, $data, $transport)
{
    try {
        //on fait le mail de rappel et renit la date a now
        $date = new DateTime();
        $date->modify("-30 minutes");
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

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM `status` 
                WHERE `status`.`name` = 'mail'";
        logInFile($link, "sql.log", $sql);
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);

        $value = $row['value'];

        if ($count && intval($count) > 10 && $value && ($value == 1 || $value == "1")) {
            $sql = "# noinspection SqlNoDataSourceInspectionForFile
                    UPDATE `status` 
                    SET `value` = '0' 
                    WHERE `status`.`name` = 'mail';";
            logInFile($link, "sql.log", $sql);
            $link->query($sql);

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

            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM `data_reacteur` 
                ORDER BY `id`  DESC 
                LIMIT 1";
            $controle = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($controle);
            $content .= "<p>Dernier débit enregistré : " . $row['value'] . " l/min</p>";

            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM `data_temperature` 
                ORDER BY `id`  DESC 
                LIMIT 1";
            $controle = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($controle);
            $content .= "<p>Dernière température enregistrée : " . round($row['value'], 2) . "°C</p>";

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
 * @return bool
 */
function isRunOver20seconds($link)
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
            $maxDate->modify('-20 seconds');
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
    }
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @param $type
 * @param $message
 * @param $subject
 * @return bool
 */
function checkParamEau($data, $transport, $link, $type, $message, $subject)
{
    try {
        $periode = '-6 days';
        $date = new DateTime();
        $date->modify($periode);
        $date = $date->format('Y-m-d H:i:s');

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE '" . $type . "' 
            AND `created_at` > '" . $date . "'";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $result = mysqli_fetch_assoc($request);

        if ($result['count'] == "0" || $result['count'] == 0) {
            $body = "<p style=\"color: red;\">" . $message . "</p>";
            sendMail($data, $transport, $subject, $body, $link);
            setLog($link, $message);

            return false;
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return true;
}

/**
 * @param $link
 * @return string
 */
function getLastTemperature($link)
{
    try {
        // dernière temperature
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `value`,`created_at` 
            FROM `data_temperature` 
            ORDER BY `data_temperature`.`id` DESC 
            LIMIT 1";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        $last_temp = round($row['value'], 2);

        if (!isset($last_temp)) {
            $last_temp = 0;
        }

        return $last_temp . " °C";

    } catch (Exception $e) {
        setLog($link, $e->getMessage());

        return '';
    }
}

/**
 * @param $link
 * @return string
 */
function getLastReacteur($link)
{
    try {
        // dernier débit
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `value`
            FROM `data_reacteur` 
            ORDER BY `data_reacteur`.`id` DESC  
            LIMIT 1";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        $last_debit = $row['value'];

        if (!isset($last_debit)) {
            $last_debit = 0;
        }

        return $last_debit . " l/min";

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

        return false;
    }
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @return bool
 */
function checkCleanReacteur($data, $transport, $link)
{
    $periode = '-15 days';
    $date = new DateTime();
    $date->modify($periode);
    $date = $date->format('Y-m-d H:i:s');
    $message = "Le réacteur n'a pas été nettoyé depuis plus de 15 jours !";

    $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count 
            FROM `data_clean_reacteur` 
            WHERE `created_at` > '" . $date . "'";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $body = "<p style=\"color: red;\">" . $message . "</p>";
        sendMail($data, $transport, "Rappel - nettoyer le reacteur", $body, $link);
        setLog($link, $message);

        return false;
    }

    return true;
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @return bool
 */
function checkCleanEcumeur($data, $transport, $link)
{
    $periode = '-30 days';
    $date = new DateTime();
    $date->modify($periode);
    $date = $date->format('Y-m-d H:i:s');
    $message = "L écumeur n'a pas été nettoyé depuis plus de 30 jours !";

    $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count 
            FROM `data_clean_ecumeur` 
            WHERE `created_at` > '" . $date . "'";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $body = "<p style=\"color: red;\">" . $message . "</p>";
        sendMail($data, $transport, "Rappel - nettoyer l écumeur", $body, $link);
        setLog($link, $message);

        return false;
    }

    return true;
}

/**
 * @param $data
 * @param $transport
 * @param $link
 * @return bool
 */
function checkCleanPompes($data, $transport, $link)
{
    $periode = '-90 days';
    $date = new DateTime();
    $date->modify($periode);
    $date = $date->format('Y-m-d H:i:s');
    $message = "Les pompes n'ont pas été nettoyé depuis plus de 90 jours !";

    $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count 
            FROM `data_clean_pompes` 
            WHERE `created_at` > '" . $date . "'";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    if ($result['count'] == "0" || $result['count'] == 0) {
        $body = "<p style=\"color: red;\">" . $message . "</p>";
        sendMail($data, $transport, "Rappel - nettoyer les pompes", $body, $link);
        setLog($link, $message);

        return false;
    }

    return true;
}

/**
 * @param $link
 * @param $type
 * @return null|string
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
    if (getConfig($link, 'config_log_in_files') == true) {
        $file = __DIR__ . "/../../var/log/" . $file;
        $fp = fopen($file, "a+");
        fwrite($fp, date("Y-m-d H:i:s") . " : " . $message . PHP_EOL);
        fwrite($fp, "------------------------------------" . PHP_EOL);
        fclose($fp);
    }
}
