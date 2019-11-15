<?php

/**
 * Tous les jours à 19h
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_rappel')) {
        return false;
    }

    $body = "";
    if ($rappel[date('D')]) {
        $body = $rappel[date('D')];
    }

    // on envoie le mail
    try {
        sendMail($data, $transport, "Rappel - ajout à faire", $body);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

} catch (Exception $e) {
    // on envoie le mail
    try {
        sendMail($data, $transport, "Cron erreur - ERREUR - ", $e->getMessage());
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

