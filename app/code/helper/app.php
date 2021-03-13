<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require 'bdd.php';
require 'const.php';

use Phelium\Component\MySQLBackup;

$array_verif = [
    CONTROLE_BAILLING,
    CONTROLE_ECUMEUR,
    CONTROLE_OSMOLATEUR,
    CONTROLE_REACTEUR,
    CONTROLE_TEMPERATURE
];

$message_body = [
    CONTROLE_BAILLING => 'Cron - ERREUR - script bailling',
    CONTROLE_ECUMEUR => 'Cron - ERREUR - script écumeur',
    CONTROLE_OSMOLATEUR => 'Cron - ERREUR - script osmolateur',
    CONTROLE_REACTEUR => 'Cron - ERREUR - script réacteur',
    CONTROLE_TEMPERATURE => 'Cron - ERREUR - script température'
];

$rappel = [
    "Mon" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color: #9B59B6">Bactérie</p><p style="color:#1ABB9C ">Algue</p>',
    "Tue" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
    "Wed" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color:#1ABB9C ">Algue</p>',
    "Thu" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p>',
    "Fri" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color:#1ABB9C ">Algue</p>',
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
 * @return bool|int
 */
function sendMail($data, $transport, $subject, $content, $link = null, $force = false)
{
    //check si la cron est activé
    if (!$force && !getStatus($link, 'mail')) {
        setLogMail($link, $subject, $content);

        return true;
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
 *
 * @return bool
 */
function getStatus($link, $name): bool
{
    try {
        return file_exists(__DIR__ . "/../../../config/" . $name);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param $link
 * @param $name
 * @return bool
 */
function getStateRelais($link, $name): bool
{
    try {
        return file_exists(__DIR__ . "/../../../statusRelais/" . $name);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

/**
 * @param        $link
 * @param        $temp
 * @param string $table
 */
function insertTemperature($link, $temp, $table = "`data_temperature_eau`"): void
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
 * @return false|string
 * @throws Exception
 */
function readFileTemperature($link)
{
    // on récupère le contenu du fichier
    if (file_exists(THERMOMETER_SENSOR_PATH)) {
        $thermometer = fopen(THERMOMETER_SENSOR_PATH, "r");
        $content = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH));
        fclose($thermometer);
    } else {
        $message = "ERREUR - Le fichier : " . THERMOMETER_SENSOR_PATH . " n'existe pas.";
        setState($link, TEMPERATURE, 'state_1', true, $message);

        throw new Exception($message);
    }

    return $content;

}

/**
 * @param $link
 *
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
 * @return bool|float
 */
function readTemperature($content)
{
    $lines = preg_split("/\n/", $content);
    $arrayFilter = array_filter($lines);
    if (!is_array($lines) || count($arrayFilter) == 0) {
        return false;
    }

    preg_match("/t=(.+)/", $lines[1], $matches);

    if (strpos($lines[0], 'NO') !== false || $matches[1] == "85000") {
        return false;
    }

    $temperature = floatval($matches[1]);
    $temperature = $temperature / 1000;

    return (float)$temperature;
}

/**
 * @param $link
 * @param $value
 */
function setControle($link, $value): void
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE " . TABLE_LAST_ACTIVITY . " set `value`='" . $value . "', `created_at`=now() 
                WHERE `value`='" . $value . "'";
        logInFile($link, "sql.log", $sql);
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
 *
 * @return bool
 */
function setState($link, $path, $value, $error, $message, $exclude = 0, $force_log = false): bool
{
    try {
        $file = __DIR__ . "/../../../state/" . $path . '-' . $value;

        if (!file_exists($file)) {
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                    UPDATE " . TABLE_STATE . " set `value`='" . $value . "',`error`='" . $error . "',`message`='" . $message . "', `created_at`=now(), `mail_send`=0, `exclude_check`='" . $exclude . "' 
                    WHERE `path`='" . $path . "'";
            logInFile($link, "sql.log", $sql);
            $link->query($sql);

            // met ligne dans table log
            setLog($link, $message);

            //on créer le ficher state
            exec("rm -f " . __DIR__ . "/../../../state/" . $path . '-*');
            exec("touch " . $file);

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
function setLog($link, $message): void
{
    $message = str_replace("'", "\'", $message);
    // met ligne dans table log
    $sql = '# noinspection SqlNoDataSourceInspectionForFile 
            INSERT INTO ' . TABLE_LOG . ' (`message`) 
            VALUES ("' . $message . '")';
    logInFile($link, "sql.log", $sql);
    $link->query($sql);
}

/**
 * @param $link
 * @param $sujet
 * @param $message
 */
function setLogMail($link, $sujet, $message): void
{
    $message = str_replace('"', "'", $message);
    $message = str_replace("'", "\'", $message);
    $sujet = str_replace("'", "\'", $sujet);
    // met ligne dans table log
    $sql = '# noinspection SqlNoDataSourceInspectionForFile 
            INSERT INTO ".TABLE_LOG_MAIL." (`sujet`, `message`) 
            VALUES ("' . $sujet . '","' . $message . '")';
    logInFile($link, "sql.log", $sql);
    $link->query($sql);
}

/**
 * @param $date
 *
 * @return string
 * @throws Exception
 */
function getFormattedDate($date): string
{
    $format = new DateTime($date);

    return $format->format('d/m/Y à H:i:s');
}

/**
 * @param $date
 *
 * @return string
 * @throws Exception
 */
function getFormattedDateWithouH($date): string
{
    $format = new DateTime($date);

    return $format->format('d/m/Y');
}

/**
 * @param $date
 * @param $link
 * @return string
 */
function getFormattedHours($date, $link): string
{
    try {
        $format = new DateTime($date);

        return $format->format('H:i');

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }

    return '';
}

/**
 * @param $date1
 * @param $date2
 * @return int
 * @throws Exception
 */
function getNumberDaysBetweenDate($date1, $date2): int
{
    $dateStart = new DateTime($date1);
    $dateStart->setTime(0, 0, 0);
    $dateEnd = new DateTime($date2);
    $dateEnd->setTime(0, 0, 0);

    $x1 = days($dateStart);
    $x2 = days($dateEnd);

    if ($x1 && $x2) {
        $result = abs($x1 - $x2);

        if ($result == 0) {
            if ($dateStart->format("Y-m-d") !== $dateEnd->format("Y-m-d")) {
                return 1;
            }
        }

        return (int)$result;
    }
}

/**
 * @param $x
 *
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
function setStatus($link, $data, $code): void
{
    try {
        if (isset($data)) {
            $value = 1;
        } else {
            $value = 0;
        }
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                UPDATE " . TABLE_STATUS . " 
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

        if ($code == 'on_off_osmolateur') {
            if ($value == 1) {
                // on allume
                exec("python " . __DIR__ . "/../../../scripts/osmolateur/on.py");
            } else {
                // on éteint
                exec("python " . __DIR__ . "/../../../scripts/osmolateur/off.py");
            }
        }

        if ($code == 'force_turn_on_eclairage') {
            if ($value == 1) {
                // on allume
                exec("python " . __DIR__ . "/../../../scripts/reacteur_eclairage/on.py");
            }
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
function setConfig($link, $data, $code): void
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
                UPDATE " . TABLE_CORE_CONFIG . " 
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
function isOn(): bool
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
function clear($link): void
{
    try {
        $date = new DateTime();
        $periode = '-30 days';
        $date->modify($periode);
        $limit = $date->format('Y-m-d H:i:s');

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_DATA_REACTEUR . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_DATA_TEMP_EAU . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_DATA_TEMP_AIR . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_DATA_TEMP_RPI . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_LOG . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_LOG_MAIL . " 
                WHERE `created_at` < '" . $limit . "';";
        $link->query($sql);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @param $currentTemperature
 *
 * @return bool
 */
function getStatusVentilateur($link, $currentTemperature): bool
{
    try {
        $result = false;
        $temperature = getConfig($link, "config_temperature_declenchement");

        if ($currentTemperature >= intval($temperature)) {
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
 * @return bool|mixed|string
 */
function getConfig($link, $name)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `value` 
                FROM " . TABLE_CORE_CONFIG . " 
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
function envoyerMail($link, $data, $transport): void
{
    try {
        // on fait le premier mail
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
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
                UPDATE " . TABLE_STATE . " 
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
function envoyerMailRappel($link, $data, $transport, $tempsMailRappel): void
{
    try {
        //on fait le mail de rappel et renit la date a now
        $date = new DateTime();
        $date->modify($tempsMailRappel);
        $date = "'" . $date->format('Y-m-d H:i:00') . "'";

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
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
                UPDATE " . TABLE_STATE . " 
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
function checkDisableSendMail($link, $data, $transport): void
{
    try {
        //on fait le mail de rappel et renit la date a now
        $date = $date2 = new DateTime();
        $date->modify("-2 minutes");
        $date = "'" . $date->format('Y-m-d H:i:00') . "'";

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT count(*) as count
            FROM " . TABLE_LOG_MAIL . " 
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
function envoyerMail8h($link, $data, $transport): void
{
    try {
        //controle 8h
        $date = new DateTime();
        $current = $date->format('Y-m-d H:i:00');
        $huit = $date->format('Y-m-d 08:00:00');

        if ($current == $huit) {
            $content = "<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>";
            $content .= "<p>Dernier débit enregistré : " . getLastData($link, "data_reacteur", " l/min") . "</p>";
            $content .= "<p>Dernière température enregistrée : " . getLastData($link, "data_temperature_eau",
                    " °C") . "</p>";

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
 *
 * @return bool
 */
function isRunOver20seconds($link, $tempsMaxPompeOsmolateur): bool
{
    try {
        // si c'est le state 3 et qu'il a moins de 20 secondes
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
            WHERE `path` LIKE '" . OSMOLATEUR . "'
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
                setState($link, OSMOLATEUR, 'state_8', 1, $message);

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
 * @return bool
 */
function isRun($link): bool
{
    try {
        // si c'est le state 3 c'est que c'est remplissage en cours
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
            WHERE `path` LIKE '" . OSMOLATEUR . "'
            AND (`value` LIKE 'state_3')";
        logInFile($link, "sql.log", $sql);
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row) {
            return true;
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return false;
}

/**
 * @param $link
 * @return bool
 */
function isRunEcumeur($link): bool
{
    try {
        // si c'est le state 2 c'est que niveau godet ok
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
            WHERE `path` LIKE '" . ECUMEUR . "'
            AND (`value` LIKE 'state_2')";
        logInFile($link, "sql.log", $sql);
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row) {
            return true;
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return false;
}

/**
 * @param $link
 * @return bool
 */
function isNiveauToHigh($link): bool
{
    try {
        // si c'est le state 1 osmolateur c'est que le niveau est trop haut
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_STATE . " 
            WHERE `path` LIKE '" . OSMOLATEUR . "'
            AND (`value` LIKE 'state_1')";
        logInFile($link, "sql.log", $sql);
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row) {
            return true;
        }
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    return false;
}

/**
 * @param $link
 * @param $data
 * @param $type
 */
function setParam($link, $data, $type): void
{
    try {
        $data = str_replace(',', '.', $data);
        if (isset($data) && is_numeric($data)) {
            $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO " . TABLE_DATA_EAU . " (`type`, `value`) 
                VALUES ('" . $type . "', '" . strval($data) . "')";
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
 * @param $message
 * @param $subject
 * @param $config_path
 * @param $sendMail
 * @return bool|string|string[]|null
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
 * @param      $data
 * @param      $transport
 * @param      $link
 * @param bool $sendMail
 *
 * @return array
 */
function allCheckLastTimeCheck($data, $transport, $link, $sendMail = true): array
{
    $result = [];

    //si pas de changement d'eau depuis plus de 15 jours on envoie un mail de rappel
    $message = "Pas de changement d'eau depuis XX jours !";
    $subject = "Rappel - faire un changement d'eau";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_changement_eau', 'type' => ''], $message,
        $subject, "check_changement_eau", $sendMail);

    //si pas nettoyé le reacteur depuis plus de 15 jours on envoie un mail de rappel
    $message = "Le réacteur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer le reacteur";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_reacteur', 'type' => ''], $message,
        $subject, "check_clean_reacteur", $sendMail);

    //si pas nettoyé le écumeur depuis plus de 30 jours on envoie un mail de rappel
    $message = "L'écumeur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer l'écumeur";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_ecumeur', 'type' => ''], $message,
        $subject, "check_clean_ecumeur", $sendMail);

    //si pas nettoyé les pompes depuis plus de 90 jours on envoie un mail de rappel
    $message = "Les pompes n'ont pas été nettoyé depuis depuis XX jours !";
    $subject = "Rappel - nettoyer les pompes";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_pompes', 'type' => ''], $message,
        $subject, "check_clean_pompes", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Ca depuis XX jours !";
    $subject = "Rappel - faire une mesure du Ca";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'ca'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Mg depuis XX jours !";
    $subject = "Rappel - faire une mesure du Mg";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'mg'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Kh depuis XX jours !";
    $subject = "Rappel - faire une mesure du Kh";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'kh'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de la densité depuis XX jours !";
    $subject = "Rappel - faire une mesure de la densité";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'densite'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Potassium depuis XX jours !";
    $subject = "Rappel - faire une mesure du Potassium";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'potassium'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de nitrate depuis XX jours !";
    $subject = "Rappel - faire une mesure de Nitrate";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'nitrate'],
        $message, $subject, "check_analyse_eau", $sendMail);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de phosphate depuis XX jours !";
    $subject = "Rappel - faire une mesure de Phosphate";
    $result[] = checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'phosphate'],
        $message, $subject, "check_analyse_eau", $sendMail);

    return $result;
}

/**
 * @param $link
 * @param $table
 * @param $suffix
 *
 * @return string
 */
function getLastData($link, $table, $suffix): string
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
 *
 * @return bool
 */
function clean($link, $type): bool
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
 *
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
function logInFile($link, $file, $message): void
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
function setMessage($type, $message): void
{
    session_start();
    $data = $_SESSION;
    $data[$type][] = $message;
    $_SESSION = $data;
    session_write_close();
}

/**
 * @param $link
 * @param $type
 * @param $evolution
 *
 * @return string
 */
function getLastParam($link, $type, $evolution): string
{
    try {
        $label = '';
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM " . TABLE_DATA_EAU . " 
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
            case 'phosphate':
            case 'nitrate':
            case 'potassium':
            case 'mg':
            case 'ca':
                $label = 'mg/l';
                break;
            case 'densite':
                $label = '';
        }

        $days = $days = getNumberDaysBetweenDate($row["created_at"], date("Y-m-d H:i:s"));

        if ($days > 1) {
            $jours = "il y a " . $days . " jours (" . getFormattedDateWithouH($row["created_at"]) . ")";
        } elseif ($days == 1) {
            $jours = "hier";
        } else {
            $jours = "aujourd'hui";
        }

        return $row["value"] . " " . $label . " " . $evolution . " " . $jours;

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @param $type
 *
 * @return string
 */
function getLastDiffParam($link, $type): string
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM " . TABLE_DATA_EAU . " 
                WHERE `type` = '" . $type . "' 
                ORDER BY `id` DESC 
                LIMIT 1";
        logInFile($link, "sql.log", $sql);

        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM " . TABLE_DATA_EAU . " 
                WHERE `type` = '" . $type . "' 
                ORDER BY `id` DESC 
                LIMIT 1,1";
        logInFile($link, "sql.log", $sql);

        $request = mysqli_query($link, $sql);
        $row2 = mysqli_fetch_assoc($request);

        $date1 = $row["created_at"];
        $date2 = $row2["created_at"];

        $days = getNumberDaysBetweenDate($date1, $date2);
        $diff = $row["value"] - $row2["value"];

        $label = '';
        switch ($type) {
            case 'kh':
                $label = 'dkh';
                break;
            case 'nitrate':
            case 'potassium':
            case 'phosphate':
            case 'mg':
            case 'ca':
                $label = 'mg/l';
                break;
            case 'densite':
                $label = '';
        }

        $signe = "";
        $style = "color:#B33A3A";
        if ($diff >= 0) {
            $style = "color:#4BB543";
            $signe = "+";
        }

        $jour = "jour";
        if ($days > 1) {
            $jour = "jours";
        }

        return "  ( <span style='" . $style . "'>" . $signe . $diff . " " . $label . " en " . $days . " " . $jour . "</span> )";

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 *
 * @return string
 */
function getLastChangementEau($link): string
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM " . TABLE_DATA_CHANGEMENT_EAU . " 
                ORDER BY `id` DESC 
                LIMIT 1";
        logInFile($link, "sql.log", $sql);

        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);

        $days = $days = getNumberDaysBetweenDate($row["created_at"], date("Y-m-d H:i:s"));

        if ($days > 1) {
            $jours = "il y a " . $days . " jours (" . getFormattedDateWithouH($row["created_at"]) . ")";
        } else if ($days == 1) {
            $jours = "hier";
        } else {
            $jours = "aujourd'hui";
        }

        return $row["value"] . " litres " . $jours;

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 *
 * @return string
 */
function getOlderData($link): string
{
    try {
        $dates = [];

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_LOG . " 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_DATA_REACTEUR . " 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_DATA_TEMP_EAU . " 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_DATA_TEMP_AIR . " 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_DATA_TEMP_RPI . " 
                ORDER BY `id` ASC 
                LIMIT 1";
        $request = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($request);
        if ($row) {
            $dates[$row["created_at"]] = new DateTime($row["created_at"]);
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT `created_at` 
                FROM " . TABLE_LOG_MAIL . " 
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

/**
 * @param $link
 * @param $name
 * @param $value
 * @return string
 * @throws Exception
 */
function getDaysBeforeAlert($link, $name, $value): string
{
    $messages = [
        "check_changement_eau" => "Pas de changement d'eau depuis XX jours !",
        "check_clean_reacteur" => "Le réacteur n'a pas été nettoyé depuis XX jours !",
        "check_clean_ecumeur" => "L'écumeur n'a pas été nettoyé depuis XX jours !",
        "check_clean_pompes" => "Les pompes n'ont pas été nettoyé depuis depuis XX jours !",
        "check_analyse_eau" => "Pas d'analyse d'eau depuis depuis XX jours !"
    ];

    $type = "";
    if ($name == "check_analyse_eau") {
        $type = "ca";
    }

    $table = str_replace('check', 'data', $name);
    $table = str_replace('analyse', 'parametres', $table);

    $result = checkLastTimeCheck("", "", $link, ['table' => $table, 'type' => $type], $messages[$name],
        "", $name, 0);

    if ($result) {
        return '<div class="alert alert-danger" role="alert">' . $result . '</div>';
    }

    $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `created_at` as created_at
            FROM `" . $table . "` 
            WHERE `id` > 0"
        . " ORDER BY `id` DESC LIMIT 1";
    logInFile($link, "sql.log", $sql);
    $request = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($request);

    $lastDate = $result["created_at"];
    $days = getNumberDaysBetweenDate($lastDate, date("Y-m-d H:i:s"));
    $leftTime = $value - $days;

    if ($leftTime == 0) {
        $time = "aujourd'hui";
    } elseif ($leftTime > 1) {
        $time = "dans " . $leftTime . " jours";
    } else {
        $time = "dans " . $leftTime . " jour";
    }

    $class = 'alert alert-info';
    if ($leftTime <= 2) {
        $class = 'alert alert-warning';
    }

    return '<div class="' . $class . '" role="alert">Prochaine alerte ' . $time . '</div>';

}

/**
 * @return array
 */
function getCrons(): array
{
    $directory = __DIR__ . '/../cron/';
    $results = [];
    if ($handle = opendir($directory)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $results[] = ['type' => $entry, 'run' => $directory . $entry];
            }
        }
        closedir($handle);

        return $results;
    }

    return [];
}

/**
 * @param $link
 * @param $data
 * @return string
 */
function getCurrentTemperature($link, $data): string
{
    try {
        // on défini le chemin du fichier
        if (!defined("THERMOMETER_SENSOR_PATH")) {
            define("THERMOMETER_SENSOR_PATH", $data['file_temperature_eau']);
        }

        $content = readFileTemperature($link);
        $temperature = readTemperature($content);

        return round($temperature, 2) . "°C";

    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

/**
 * @param $link
 * @return false|string
 */
function getContentTempFileCron($link)
{
    try {
        $content = readFileTemperature($link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        $content = "";
    }

    return $content;
}

/**
 * @param $data
 * @param $transport
 * @param $subject
 * @return int
 */
function dumpBDD($data, $transport, $subject): int
{
    $date = date('Ymd-H\hi');
    $file = __DIR__ . "/../../../var/backup/dump_{$data['database'][0]['database']}_{$date}";

    $Dump = new MySQLBackup(
        $data['database'][0]['host'],
        $data['database'][0]['user'],
        $data['database'][0]['passwd'],
        $data['database'][0]['database']
    );
    $Dump->setFilename($file);
    $Dump->setCompress('zip');
    $Dump->dump();

    $mailer = new Swift_Mailer($transport);
    $message = new Swift_Message($subject);
    $message
        ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
        ->setTo([$data['mail_to']])
        ->setSubject($subject)
        ->setBody($subject, 'text/html')
        ->attach(Swift_Attachment::fromPath("{$file}.zip"));

    return $mailer->send($message);

}

/**
 * @param $link
 * @param $type
 * @return string
 */
function insertTimer($link, $type): string
{
    try {
        $periode = $temperature = getConfig($link, "timer_" . $type);

        $date = new DateTime();
        $date->modify($periode);
        $date = $date->format('Y-m-d H:i:s');

        $sql = '# noinspection SqlNoDataSourceInspectionForFile 
                INSERT INTO ' . TABLE_TIMER . ' ( `type`, `off_until`) 
                VALUES ("' . $type . '","' . $date . '")
                ON DUPLICATE KEY UPDATE `off_until` = "' . $date . '"';
        logInFile($link, "sql.log", $sql);
        $link->query($sql);

        return $date;

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }
}

/**
 * @param $link
 * @param $type
 * @return bool
 */
function haveTimer($link, $type): bool
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT * 
            FROM " . TABLE_TIMER . " 
            WHERE `type` LIKE '" . $type . "'";
        logInFile($link, "sql.log", $sql);
        $timer = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($timer);

        if ($row) {
            return true;
        }

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }

    return false;
}

/**
 * @param $link
 * @param $type
 * @return bool
 */
function removeTimer($link, $type): bool
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                DELETE FROM " . TABLE_TIMER . " 
                WHERE `type` LIKE '" . $type . "';";
        $link->query($sql);

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }

    return false;
}

/**
 * @param $link
 * @param $type
 * @return mixed|string|null
 */
function getTimer($link, $type)
{
    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `off_until`
            FROM " . TABLE_TIMER . " 
            WHERE `type` LIKE '" . $type . "'";
        logInFile($link, "sql.log", $sql);
        $timer = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($timer);

        if ($row && $row['off_until']) {
            return $row['off_until'];
        }

    } catch (Exception $e) {
        setLog($link, $e->getMessage());
        setMessage("error", $e->getMessage());
    }

    return null;
}

/**
 * @param $value
 * @param $max
 * @return false|float|int
 */
function getTransitiongoal($value, $max)
{
    if ($value >= $max) {
        return 100;
    }

    return ceil($value * 100 / $max);
}

/**
 * @param $link
 * @param $table
 * @param string $type
 * @return int
 */
function getDaysProgess($link, $table, $type = '')
{
    try {
        $where = '';
        if ($type) {
            $where = "WHERE `type` = '{$type}'";
        }

        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `created_at` 
            FROM `{$table}`
            {$where}
            ORDER BY `id` DESC
            LIMIT 1;";
        $request = mysqli_query($link, $sql);
        $date = mysqli_fetch_assoc($request);

        return getNumberDaysBetweenDate($date["created_at"], date("Y-m-d H:i:s"));
    } catch (Exception $e) {
        return 0;
    }

}