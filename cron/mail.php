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

    envoyerMail($link, $data, $transport);
    envoyerMailRappel($link, $data, $transport);

    envoyerMail8h($link, $data, $transport);

} catch (Exception $e) {
    setLog($link, $e->getMessage());
}

