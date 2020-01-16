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

    // on envoie le mail avec les rappels du jour
    sendMail($data, $transport, "Rappel - ajout à faire", $body);

    //si pas de changement d'eau depuis plus de 15 jours on envoie un mail de rappel
    checkChangementEau($data, $transport, $link);

} catch (Exception $e) {
    // on envoie le mail
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Cron erreur - ERREUR - ", $e->getMessage());
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}

