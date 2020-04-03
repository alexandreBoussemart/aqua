<?php

require '../cron/helper/functions.php';

$count_osmolateur = 0;
$date = new DateTime();

$day_name = date('D');

$periode = '-1 day';

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

    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php"); ///aqua-web
}

//form action clear data
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php"); ///aqua-web
}

//form action clean reacteur
if (isset($_POST['submit_actions_clear_reacteur'])) {
    clean($link, "reacteur");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php"); ///aqua-web
}

//form action clean ecumeur
if (isset($_POST['submit_actions_clear_ecumeur'])) {
    clean($link, "ecumeur");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php"); ///aqua-web
}

//form action clean pompes
if (isset($_POST['submit_actions_clear_pompes'])) {
    clean($link, "pompes");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php"); ///aqua-web
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

    header('Location: ' . $data['database'][0]['base_url'] . "logs.php"); ///aqua-web
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    setConfig($link, $_POST['config_temperature_declenchement'], 'config_temperature_declenchement');
    setConfig($link, $_POST['config_on_off_osmolateur'], 'config_on_off_osmolateur');
    setConfig($link, $_POST['config_on_off_ecumeur'], 'config_on_off_ecumeur');
    setConfig($link, $_POST['config_log_in_files'], 'config_log_in_files');
    setConfig($link, $_POST['check_changement_eau'], 'check_changement_eau');
    setConfig($link, $_POST['check_clean_reacteur'], 'check_clean_reacteur');
    setConfig($link, $_POST['check_clean_ecumeur'], 'check_clean_ecumeur');
    setConfig($link, $_POST['check_clean_pompes'], 'check_clean_pompes');
    setConfig($link, $_POST['temperature_min'], 'temperature_min');
    setConfig($link, $_POST['temperature_max'], 'temperature_max');
    setConfig($link, $_POST['check_analyse_eau'], 'check_analyse_eau');

    header('Location: ' . $data['database'][0]['base_url'] . "configuration.php");
}

// form param d'eau
if (isset($_POST['submit_params'])) {
    foreach ($_POST as $key => $value){
        setParam($link, $value, str_replace("value_", "", $key));
    }

    header('Location: ' . $data['database'][0]['base_url']);
}


