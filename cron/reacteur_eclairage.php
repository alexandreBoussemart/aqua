<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    $date = new DateTime();
    $now = $date->format('Y-m-d H:i:s');

    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_eclairage')) {
        //check les horaires
        if(
            ($now >= $date->format('Y-m-d 22:30:00') && $now < $date->format('Y-m-d 23:30:00')) ||
            ($now >= $date->format('Y-m-d 23:40:00') || $now < $date->format('Y-m-d 00:40:00')) ||
            ($now >= $date->format('Y-m-d 00:50:00') && $now < $date->format('Y-m-d 01:50:00')) ||
            ($now >= $date->format('Y-m-d 02:00:00') && $now < $date->format('Y-m-d 03:00:00')) ||
            ($now >= $date->format('Y-m-d 03:10:00') && $now < $date->format('Y-m-d 04:10:00')) ||
            ($now >= $date->format('Y-m-d 04:20:00') && $now < $date->format('Y-m-d 05:20:00')) ||
            ($now >= $date->format('Y-m-d 05:30:00') && $now < $date->format('Y-m-d 06:30:00')) ||
            ($now >= $date->format('Y-m-d 06:40:00') && $now < $date->format('Y-m-d 07:40:00')) ||
            ($now >= $date->format('Y-m-d 07:50:00') && $now < $date->format('Y-m-d 08:50:00')) ||
            ($now >= $date->format('Y-m-d 09:00:00') && $now < $date->format('Y-m-d 10:00:00'))
        ) {
            // on allume

            return true;
        }
    }

    // on éteind


} catch (Exception $e) {
    setLog($link, $e->getMessage());
}



