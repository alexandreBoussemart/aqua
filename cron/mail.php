<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_mail')) {
        return false;
    }

    // on fait le premier mail
    $sql = "SELECT * FROM `state` WHERE `exclude_check` LIKE 0 AND `mail_send` LIKE 0";
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
            sendMail($data, $transport, $message, $body);
        } catch (Exception $e) {
            setLog($link, $e->getMessage());
        }

        // on set comme quoi le mail a été envoyé et on renit la date
        $sql = "UPDATE `state` SET `mail_send`=1,`created_at`=now() WHERE `id` LIKE " . $id;
        $link->query($sql);
    }

    //on fait le mail de rappel et renit la date a now
    $date = new DateTime();
    $date->modify("-30 minutes");
    $date = "'" . $date->format('Y-m-d H:i:00') . "'";

    $sql = "SELECT * FROM `state` WHERE `exclude_check` LIKE 0 AND `error` LIKE 1 AND `created_at` < " . $date;
    $mails = mysqli_query($link, $sql);
    $rows = $mails->fetch_all();

    foreach ($rows as $row) {
        $id = $row[0];
        $message = $row[5];
        $error = $row[4];

        $message = str_replace('ERREUR', 'RAPPEL ERREUR', $message);
        $body = "<p style='color: red; text-transform: uppercase'>" . $message . "</p>";

        // on envoie le mail
        try {
            sendMail($data, $transport, $message, $body);
        } catch (Exception $e) {
            setLog($link, $e->getMessage());
        }

        // on renit la date
        $sql = "UPDATE `state` SET `created_at`=now() WHERE `id` LIKE " . $id;
        $link->query($sql);
    }

    //controle 8h
    $date = new DateTime();
    $current = $date->format('Y-m-d H:i:00');
    $huit = $date->format('Y-m-d 08:00:00');

    if ($current == $huit) {
        $content = "<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>";

        $sql = "SELECT `value` FROM `reacteur` ORDER BY `reacteur`.`id`  DESC LIMIT 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);
        $content .= "<p>Dernier débit enregistré : " . $row['value'] . " l/min</p>";

        $sql = "SELECT `value` FROM `temperature` ORDER BY `temperature`.`id`  DESC LIMIT 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);
        $content .= "<p>Dernière température enregistrée : " . round($row['value'], 2) . "°C</p>";

        $message = "Cron - contrôle 8h - OK";

        // on envoie le mail
        sendMail($data, $transport, $message, $content);
    }

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}

