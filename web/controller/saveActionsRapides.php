<?php

require __DIR__ . '/../../app/code/helper/app.php';

// form actions rapides
setStatus($link, isset($_POST['on_off_osmolateur']) ? $_POST['on_off_osmolateur'] : null, 'on_off_osmolateur');
setStatus($link, isset($_POST['on_off_ecumeur']) ? $_POST['on_off_ecumeur'] : null, 'on_off_ecumeur');

$message = "Validation du formulaire actions rapides.";
sendMail($data, $transport, $message, $message, $link);