<?php

require __DIR__ . '/../../app/code/helper/app.php';

// form actions rapides
foreach (['on_off_osmolateur', 'on_off_ecumeur'] as $code) {
    setStatus($link, $_POST[$code] ?? null, $code);
}

$message = "Validation du formulaire actions rapides.";
sendMail($data, $transport, $message, $message, $link);
