<?php

require_once '../vendor/autoload.php';
require '../cron/helper/bdd.php';

/**
 * @param $key
 * @return mixed
 */
function getLabel($key)
{
    $array = [
        'off' => "Off",
        'ok' => "Niveau d'eau OK",
        "pump_on" => "En cours de remplissage",
        "to_low" => "Niveau d'eau bas",
        "to_high" => "Niveau d'eau haut",
        "off_rappel" => "RAPPEL - Off",
        "to_low_rappel" => "RAPPEL - Niveau d'eau bas",
        "pump_on_20" => "Pompe allumée plus de 20 secondes",
        "pump_on_20_rappel" => "RAPPEL - Pompe allumée plus de 20 secondes",
        "to_high_rappel" => "RAPPEL - Niveau d'eau haut"
    ];

    return $array[$key];
}

/**
 * @param $link
 * @param $code
 * @return mixed
 */
function getStatus($link, $code)
{
    $sql = "SELECT `value` FROM `status` WHERE `name` = '" . $code . "' LIMIT 1";
    $request = mysqli_query($link, $sql);
    return mysqli_fetch_assoc($request)['value'];
}


