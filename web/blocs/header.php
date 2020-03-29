<?php

require '../cron/helper/functions.php';

$count_osmolateur = 0;
$date = new DateTime();

$period = 1;
if (isset($_GET['period'])) {
    $period = $_GET['period'];
}

$day_name = date('D');

if ($period == 1) {
    $periode = '-1 day';
} elseif ($period == 2) {
    $periode = '-2 days';
} elseif ($period == 7) {
    $periode = '-7 days';
} else {
    $periode = '-1 day';
}

$date = new DateTime();
$today = $date->format('Y-m-d H:i:s');
$date->modify($periode);
$yesterday = $date->format('Y-m-d H:i:s');

// form statut
if (isset($_POST['submit'])) {
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

    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//form action clear
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//form action clean reacteur
if (isset($_POST['submit_actions_clear_reacteur'])) {
    cleanReacteur($link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

// form changement d'eau
if (isset($_POST['submit_eau'])) {
    if (isset($_POST['value']) && is_numeric($_POST['value'])) {
        $sql = 'INSERT INTO `data_changement_eau` ( `value`) VALUES ("' . strval($_POST['value']) . '")';
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//delete value changement d'eau
if (isset($_POST['submit_delete_eau'])) {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sql = 'DELETE FROM `data_changement_eau` WHERE `id` LIKE ' . $_POST['id'] . ';';
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    setConfig($link, $_POST['config_temperature_declenchement'], 'config_temperature_declenchement');
    setConfig($link, $_POST['config_on_off_osmolateur'], 'config_on_off_osmolateur');
    setConfig($link, $_POST['config_on_off_ecumeur'], 'config_on_off_ecumeur');
    setConfig($link, $_POST['config_log_in_files'], 'config_log_in_files');

    header('Location: ' . $data['database'][0]['base_url']);
}

// form param d'eau
if (isset($_POST['submit_params'])) {
    setParam($link, $_POST['value_kh'], 'kh');
    setParam($link, $_POST['value_ca'], 'ca');
    setParam($link, $_POST['value_mg'], 'mg');
    setParam($link, $_POST['value_densite'], 'densite');

    header('Location: ' . $data['database'][0]['base_url']);
}


