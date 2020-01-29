<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    // controle toutes les 1 seconde
    for ($i = 0; $i <= 60; $i++) {
        sleep(1);

        if (getConfig($link, 'config_on_off_osmolateur') == true) {
            // on allume
            exec("python " . __DIR__ . "/../scripts/on_osmolateur.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../scripts/off_osmolateur.py");
        }

        if (getConfig($link, 'config_on_off_reacteur') == true) {
            // on allume
            exec("python " . __DIR__ . "/../scripts/on_reacteur.py");
        } else {
            // on éteint
            exec("python " . __DIR__ . "/../scripts/off_reacteur.py");
        }
    }

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}



