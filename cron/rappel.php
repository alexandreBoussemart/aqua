<?php

/**
 * Tous les jours à 19h
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getConfig($link, 'cron_rappel')) {
        return false;
    }

    if($rappel[date('D')]){
        $body = $rappel[date('D')];
    }

    sendMail($data, $transport, "Rappel - ajout à faire", $body);

} catch (Exception $e) {
    sendMail($data, $transport, "Cron erreur - ERREUR - ", $e->getMessage());
}

