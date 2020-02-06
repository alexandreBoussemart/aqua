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

$sql = "SELECT * FROM `data_temperature` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$temperature = mysqli_query($link, $sql);

$sql = "SELECT * FROM `data_reacteur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$reacteur = mysqli_query($link, $sql);

$sql = "SELECT * FROM `data_osmolateur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' order by created_at DESC";
$osmo = mysqli_query($link, $sql);

$sql = "SELECT count(*) as somme FROM `data_osmolateur` WHERE `state` = 'pump_on' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$count = mysqli_query($link, $sql);
while ($obj = $count->fetch_object()) {
    $count_osmolateur = $obj->somme;
}

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
    setStatus($link, $_POST['cron_mail'], 'cron_mail');
    setStatus($link, $_POST['refroidissement'], 'refroidissement');

    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//form action clear
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//liste des status
$sql = "SELECT * FROM `status`";
$listes_status = mysqli_query($link, $sql);

//liste des controles
$sql = "SELECT * FROM `last_activity`";
$listes_controles = mysqli_query($link, $sql);

// logs
$sql = "SELECT * FROM `log` ORDER BY `id` DESC LIMIT 30;";
$request = mysqli_query($link, $sql);
$logs = mysqli_query($link, $sql);

// changement eau
$sql = "SELECT * FROM `data_changement_eau` ORDER BY `id` DESC LIMIT 30;";
$request = mysqli_query($link, $sql);
$changements = mysqli_query($link, $sql);

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

$sql = "SELECT * FROM `core_config`";
$core_config = mysqli_query($link, $sql);
$core_config = mysqli_fetch_all($core_config);
$config = [];
foreach ($core_config as $c) {
    $config[$c[1]] = $c[2];
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    setConfig($link, $_POST['config_temperature_declenchement'], 'config_temperature_declenchement');
    setConfig($link, $_POST['config_on_off_osmolateur'], 'config_on_off_osmolateur');
    setConfig($link, $_POST['config_on_off_ecumeur'], 'config_on_off_ecumeur');

    header('Location: ' . $data['database'][0]['base_url']);
}

// form kh
if (isset($_POST['submit_kh'])) {
    setParam($link, $_POST['value_kh'], 'kh');

    header('Location: ' . $data['database'][0]['base_url']);
}

// form ca
if (isset($_POST['submit_ca'])) {
    setParam($link, $_POST['value_ca'], 'ca');

    header('Location: ' . $data['database'][0]['base_url']);
}

// form mg
if (isset($_POST['submit_mg'])) {
    setParam($link, $_POST['value_mg'], 'mg');
    header('Location: ' . $data['database'][0]['base_url']);
}

// form densite
if (isset($_POST['submit_densite'])) {
    setParam($link, $_POST['value_densite'], 'densite');

    header('Location: ' . $data['database'][0]['base_url']);
}

$sql = "SELECT * FROM `data_parametres_eau` WHERE `type` LIKE 'ca' ORDER BY `id` ASC LIMIT 15";
$ca = mysqli_query($link, $sql);

$sql = "SELECT * FROM `data_parametres_eau` WHERE `type` LIKE 'kh' ORDER BY `id` ASC LIMIT 15";
$kh = mysqli_query($link, $sql);

$sql = "SELECT * FROM `data_parametres_eau` WHERE `type` LIKE 'mg' ORDER BY `id` ASC LIMIT 15";
$mg = mysqli_query($link, $sql);

$sql = "SELECT * FROM `data_parametres_eau` WHERE `type` LIKE 'densite' ORDER BY `id` ASC LIMIT 15";
$densite = mysqli_query($link, $sql);
