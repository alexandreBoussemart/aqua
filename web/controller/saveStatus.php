<?php

require __DIR__ . '/../../app/code/helper/app.php';

// form statut : chaque case cochée => 1, absente => 0
$codes = [
    OSMOLATEUR, ECUMEUR, BAILLING, REACTEUR, TEMPERATURE, TEMPERATURE_AIR, TEMPERATURE_RPI,
    'reacteur_ventilateur', 'reacteur_eclairage', 'cron_controle', 'cron_rappel', 'mail',
    'on_off_osmolateur', 'on_off_ecumeur', 'log_in_files', 'force_turn_on_eclairage',
    'force_turn_off_ventilateur', DISABLE_ALL_CRON,
];
foreach ($codes as $code) {
    setStatus($link, $_POST[$code] ?? null, $code);
}

$message = "Validation du formulaire de changement de statut.";
sendMail($data, $transport, $message, $message, $link);
