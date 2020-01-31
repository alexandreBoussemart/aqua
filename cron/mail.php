<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_mail')) {
        return false;
    }

    // controle mail chaque seconde
    for ($i = 0; $i <= 60; $i++) {
        envoyerMail($link, $data, $transport);
        envoyerMailRappel($link, $data, $transport);

        sleep(1);
    }

    envoyerMail8h($link, $data, $transport);

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}

