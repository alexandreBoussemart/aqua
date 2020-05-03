<?php

require '../../cron/helper/functions.php';

// form statut
setStatus($link, $_POST['osmolateur'], 'osmolateur');
setStatus($link, $_POST['ecumeur'], 'ecumeur');
setStatus($link, $_POST['bailling'], 'bailling');
setStatus($link, $_POST['reacteur'], 'reacteur');
setStatus($link, $_POST['temperature'], 'temperature');
setStatus($link, $_POST['reacteur_ventilateur'], 'reacteur_ventilateur');
setStatus($link, $_POST['reacteur_eclairage'], 'reacteur_eclairage');
setStatus($link, $_POST['cron_controle'], 'cron_controle');
setStatus($link, $_POST['cron_rappel'], 'cron_rappel');
setStatus($link, $_POST['mail'], 'mail');
setStatus($link, $_POST['refroidissement'], 'refroidissement');
setStatus($link, $_POST['on_off_osmolateur'], 'on_off_osmolateur');
setStatus($link, $_POST['on_off_ecumeur'], 'on_off_ecumeur');
setStatus($link, $_POST['log_in_files'], 'log_in_files');
setStatus($link, $_POST['force_stop_refroidissement'], 'force_stop_refroidissement');
