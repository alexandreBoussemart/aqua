<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../Helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_controle')) {
        exit;
    }

    //date now - 1 minute
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:59');
    $date->modify("-1 minute");
    $yesterday = $date->format('Y-m-d H:i:00');

    foreach ($array_verif as $verif) {
        //on prend que les lignes avec mail datant de -1 minutes
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
                SELECT * 
                FROM `last_activity` 
                WHERE `value` = '" . $verif . "' 
                AND `created_at` >= '" . $yesterday . "' 
                AND `created_at` <= '" . $today . "' 
                LIMIT 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row == null) {
            //on fait une deuxième verif au bout de 10 secondes
            sleep(10);
            $sql = "# noinspection SqlNoDataSourceInspectionForFile
                    SELECT * 
                    FROM `last_activity` 
                    WHERE `value` = '" . $verif . "' 
                    AND `created_at` >= '" . $yesterday . "' 
                    AND `created_at` <= '" . $today . "' 
                    LIMIT 1";
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

    setState($link, 'controle', 'state_1', 0, "Cron controle - OK", 1, 0);

    exit;

} catch (Exception $e) {
    setLog($link, $e->getMessage());
    $message = "Cron controle - ERREUR - " . $e->getMessage();
    setState($link, 'controle', 'state_2', 1, $message, 0, 0);

    exit;
}



