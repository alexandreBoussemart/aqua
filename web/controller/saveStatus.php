<?php

require __DIR__ . '/../../app/code/Helper/functions.php';

// form statut
setStatus($link, isset($_POST['osmolateur']) ? $_POST['osmolateur'] : null, 'osmolateur');
setStatus($link,isset($_POST['ecumeur']) ? $_POST['ecumeur'] : null, 'ecumeur');
setStatus($link, isset($_POST['bailling']) ? $_POST['bailling'] : null, 'bailling');
setStatus($link, isset($_POST['reacteur']) ? $_POST['reacteur'] : null, 'reacteur');
setStatus($link, isset($_POST['temperature']) ? $_POST['temperature'] : null, 'temperature');
setStatus($link, isset($_POST['reacteur_ventilateur']) ? $_POST['reacteur_ventilateur'] : null, 'reacteur_ventilateur');
setStatus($link, isset($_POST['reacteur_eclairage']) ? $_POST['reacteur_eclairage'] : null, 'reacteur_eclairage');
setStatus($link, isset($_POST['cron_controle']) ? $_POST['cron_controle'] : null, 'cron_controle');
setStatus($link, isset($_POST['cron_rappel']) ? $_POST['cron_rappel'] : null, 'cron_rappel');
setStatus($link, isset($_POST['mail']) ? $_POST['mail'] : null, 'mail');
setStatus($link, isset($_POST['refroidissement']) ? $_POST['refroidissement'] : null, 'refroidissement');
setStatus($link, isset($_POST['on_off_osmolateur']) ? $_POST['on_off_osmolateur'] : null, 'on_off_osmolateur');
setStatus($link, isset($_POST['on_off_ecumeur']) ? $_POST['on_off_ecumeur'] : null, 'on_off_ecumeur');
setStatus($link, isset($_POST['log_in_files']) ? $_POST['log_in_files'] : null, 'log_in_files');
setStatus($link, isset($_POST['force_stop_refroidissement']) ? $_POST['force_stop_refroidissement'] : null, 'force_stop_refroidissement');
