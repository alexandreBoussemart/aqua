<?php

try {
    $strJsonFileContents = file_get_contents(__DIR__ . "/../../config.json");
    $data = json_decode($strJsonFileContents, true);

    $transport = new Swift_SmtpTransport($data['gmail'][0]['server'], $data['gmail'][0]['port_php'], 'ssl');
    $transport->setUsername($data['gmail'][0]['mail'])->setPassword($data['gmail'][0]['password']);

    $link = new mysqli($data['database'][0]['host'], $data['database'][0]['user'], $data['database'][0]['passwd'],
        $data['database'][0]['database']);
    if ($link->connect_error) {
        sendMail($data, $transport, "Cron - ERREUR",
            'Connect Error (' . $link->connect_errno . ') ' . $link->connect_error);
    }
} catch (Exception $e) {
    sendMail($data, $transport, "Cron - ERREUR", $e->getMessage());
}
