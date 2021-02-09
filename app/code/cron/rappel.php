<?php

/**
 * Tous les jours à 19h
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    //check si la cron est activé
    if (!getStatus($link, 'cron_rappel')) {
        exit;
    }

    $body = "";
    if ($rappel[date('D')]) {
        $body = $rappel[date('D')];
    }

    // on envoie le mail avec les rappels du jour
    sendMail($data, $transport, "Rappel - ajout à faire", $body, $link);

    // on fait tous les checks
    allCheckLastTimeCheck($data, $transport, $link, true);

    exit;

} catch (Exception $e) {
    // on envoie le mail
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Cron erreur - ERREUR - ", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

