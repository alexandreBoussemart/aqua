<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    //heure d'execution max
    $date = new DateTime();
    $end = $date->format('Y-m-d H:i:59');
    $tempsMailRappel = getConfig($link, 'temps_rappel_mail');

    // controle mail chaque seconde
    for ($i = 0; $i <= 60; $i++) {
        //si on passe la minute en cours on arrête
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if ($now > $end) {
            break;
        }

        envoyerMail($link, $data, $transport);
        envoyerMailRappel($link, $data, $transport, $tempsMailRappel);

        sleep(2);
    }

    envoyerMail8h($link, $data, $transport);

    checkDisableSendMail($link, $data, $transport);

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Error script mail.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

