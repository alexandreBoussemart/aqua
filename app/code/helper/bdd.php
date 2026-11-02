<?php

// PHP >= 8.1 : mysqli lève des exceptions par défaut. Le code (écrit pour PHP 7) teste les valeurs de retour
// et connect_error : on rétablit le comportement historique.
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $strJsonFileContents = file_get_contents(__DIR__ . "/../../../config.json");
    $data = json_decode($strJsonFileContents, true);

    $transport = createMailerTransport($data);

    $link = new mysqli($data['database'][0]['host'], $data['database'][0]['user'], $data['database'][0]['passwd'],
        $data['database'][0]['database']);
    if ($link->connect_error) {
        try {
            sendMail($data, $transport, "Cron - ERREUR - connexion BDD",
                'Connect Error (' . $link->connect_errno . ') ' . $link->connect_error);
        } catch (Exception $e) {
            setLog($link, 'Connect Error (' . $link->connect_errno . ') ' . $link->connect_error);
        }
    }
} catch (Exception $e) {
    try {
        sendMail($data, $transport, "Cron - ERREUR", $e->getMessage());
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }
}
