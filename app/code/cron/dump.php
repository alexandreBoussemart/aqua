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

    dumpBDD($data, $transport, 'Backup BDD');

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Erreur script dump.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

