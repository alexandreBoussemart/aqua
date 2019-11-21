<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    $date = new DateTime();
    $now = $date->format('Y-m-d H:i:s');

    //check si on doit allumer ventilateur refroidissement
    if (getStatus($link, 'refroidissement')) {
        // on allume
        exec("python ".__DIR__."/../scripts/on_refroidissement.py");

        return true;
    }

    if(
        ($now <= $date->format('Y-m-d 22:30:00') && $now >= $date->format('Y-m-d 10:00:00'))
    ) {
        // on allume
        exec("python ".__DIR__."/../scripts/on_refroidissement.py");

        return true;
    }

    // on eteint
    exec("python ".__DIR__."/../scripts/off_refroidissement.py");

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}
