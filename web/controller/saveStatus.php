<?php

require __DIR__ . '/../../app/code/helper/app.php';

// form statut
setStatus($link, isset($_POST[OSMOLATEUR]) ? $_POST[OSMOLATEUR] : null, OSMOLATEUR);
setStatus($link,isset($_POST[ECUMEUR]) ? $_POST[ECUMEUR] : null, ECUMEUR);
setStatus($link, isset($_POST[BAILLING]) ? $_POST[BAILLING] : null, BAILLING);
setStatus($link, isset($_POST[REACTEUR]) ? $_POST[REACTEUR] : null, REACTEUR);
setStatus($link, isset($_POST[TEMPERATURE]) ? $_POST[TEMPERATURE] : null, TEMPERATURE);
setStatus($link, isset($_POST['reacteur_ventilateur']) ? $_POST['reacteur_ventilateur'] : null, 'reacteur_ventilateur');
setStatus($link, isset($_POST['reacteur_eclairage']) ? $_POST['reacteur_eclairage'] : null, 'reacteur_eclairage');
setStatus($link, isset($_POST['cron_controle']) ? $_POST['cron_controle'] : null, 'cron_controle');
setStatus($link, isset($_POST['cron_rappel']) ? $_POST['cron_rappel'] : null, 'cron_rappel');
setStatus($link, isset($_POST['mail']) ? $_POST['mail'] : null, 'mail');
setStatus($link, isset($_POST['on_off_osmolateur']) ? $_POST['on_off_osmolateur'] : null, 'on_off_osmolateur');
setStatus($link, isset($_POST['on_off_ecumeur']) ? $_POST['on_off_ecumeur'] : null, 'on_off_ecumeur');
setStatus($link, isset($_POST['log_in_files']) ? $_POST['log_in_files'] : null, 'log_in_files');
setStatus($link, isset($_POST['force_turn_on_eclairage']) ? $_POST['force_turn_on_eclairage'] : null, 'force_turn_on_eclairage');
setStatus($link, isset($_POST[DISABLE_ALL_CRON]) ? $_POST[DISABLE_ALL_CRON] : null, DISABLE_ALL_CRON);

$message = "Validation du formulaire de changement de statut.";
sendMail($data, $transport, $message, $message, $link);