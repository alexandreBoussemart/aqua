<?php

require '../cron/helper/functions.php';

$count_osmolateur = $last_debit = $last_temp = 0;
$date = new DateTime();
$date_debit = $date_temp = $date->format('Y-m-d H:i:s');

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

$sql = "SELECT * FROM `temperature` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$temperature = mysqli_query($link, $sql);

$sql = "SELECT * FROM `reacteur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$reacteur = mysqli_query($link, $sql);

$sql = "SELECT * FROM `osmolateur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' order by created_at DESC";
$osmo = mysqli_query($link, $sql);

$sql = "SELECT count(*) as somme FROM `osmolateur` WHERE `state` = 'pump_on' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
$count = mysqli_query($link, $sql);
while ($obj = $count->fetch_object()) {
    $count_osmolateur = $obj->somme;
}

$sql = "SELECT * FROM `state` WHERE `path` = 'ecumeur' LIMIT 1";
$ecumeur = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($ecumeur);
$state_ecumeur = $row['value'];
$date_ecumeur = getFormattedDate($row['created_at']);

$sql = "SELECT * FROM `state` WHERE `path` = 'bailling'";
$bailling = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($bailling);
$state_bailling = str_split($row['value']);
$date_bailling = getFormattedDate($row['created_at']);

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
    setStatus($link, $_POST['cron_temperature'], 'cron_temperature');
    setStatus($link, $_POST['cron_rappel'], 'cron_rappel');
    setStatus($link, $_POST['cron_mail'], 'cron_mail');
    setStatus($link, $_POST['refroidissement'], 'refroidissement');

    header('Location: '.$data['database'][0]['base_url']); ///aqua-web
}

// dernier débit
$sql = "SELECT `value`,`created_at` FROM `reacteur` ORDER BY `reacteur`.`id`  DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($request);
$last_debit = $row['value'];
$date_debit = getFormattedDate($row['created_at']);

// dernière temperature
$sql = "SELECT `value`,`created_at` FROM `temperature` ORDER BY `temperature`.`id` DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($request);
$last_temp = round($row['value'], 2);
$date_temp = getFormattedDate($row['created_at']);

//liste des status
$sql = "SELECT * FROM `status`";
$listes_status = mysqli_query($link, $sql);

//liste des controles
$sql = "SELECT * FROM `controle`";
$listes_controles = mysqli_query($link, $sql);

//dernier osmo
$sql = "SELECT * FROM `osmolateur` ORDER BY `osmolateur`.`id` DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$last_omo = mysqli_fetch_assoc($request);