<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron')) {
        return false;
    }

    //date now - 1 minute
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:59');
    $date->modify("-1 minute");
    $yesterday = $date->format('Y-m-d H:i:00');

    foreach ($array_verif as $verif) {
        //on prend que les lignes avec mail datant de -1 minutes
        $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row == null) {
            //on fait une deuxième verif au bout de 10 secondes
            sleep(10);
            $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
            $controle = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($controle);

            if ($row == null) {
                setState($link, $verif, 'state_2', 1, $message_body[$verif]);
            } else {
                setState($link, $verif, 'state_1', 0, "Cron controle " . $verif . " - OK");
            }

        } else {
            setState($link, $verif, 'state_1', 0, "Cron controle " . $verif . " - OK");
        }
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

        sendMail($data, $transport, "Cron - contrôle 8h - OK", $content);
    }

    setState($link, 'controle', 'state_1', 0, "Cron controle - OK");

} catch (Exception $e) {
    setState($link, 'controle', 'state_2', 1, "Cron controle - ERREUR - " . $e->getMessage());
}



