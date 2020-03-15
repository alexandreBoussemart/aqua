<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_mail')) {
        exit;
    }

    //heure d'execution max
    $date = new DateTime();
    $end = $date->format('Y-m-d H:i:59');

    // controle mail chaque seconde
    for ($i = 0; $i <= 60; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        envoyerMail($link, $data, $transport);
        envoyerMailRappel($link, $data, $transport);

        sleep(1);
    }

    envoyerMail8h($link, $data, $transport);

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script mail.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

