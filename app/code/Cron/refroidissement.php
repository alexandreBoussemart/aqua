<?php

/**
 * Toutes les minutes
 */

require '../Helper/functions.php';

try {
    $date = new DateTime();
    $now = $date->format('Y-m-d H:i:s');

    //check si on doit allumer ventilateur refroidissement
    if (getStatus($link, 'refroidissement')
        && !getStatus($link, 'force_stop_refroidissement')) {
        // on allume
        exec("python " . __DIR__ . "/../../../scripts/refroidissement/on.py");

        exit;
    }

    if (
    ($now <= $date->format('Y-m-d 22:30:00')
        && $now >= $date->format('Y-m-d 10:00:00')
        && !getStatus($link, 'force_stop_refroidissement'))
    ) {
        // on allume
        exec("python " . __DIR__ . "/../../../scripts/refroidissement/on.py");

        exit;
    }

    // on eteint
    exec("python " . __DIR__ . "/../../../scripts/refroidissement/off.py");

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script refroidissement.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}
