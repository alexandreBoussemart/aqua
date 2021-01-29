<?php

/**
 * Tous les jours à 19h
 */

require __DIR__ . '/../helper/functions.php';

try {
    // désactive toutes les crons
    if (getStatus($link, 'disable_all_cron')) {
        exit;
    }

    dumpBDD($data, $transport, 'Backup BDD');

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script dump.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

