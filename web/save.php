<?php

require __DIR__ . '/../app/code/Helper/functions.php';

//form action clear data
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    setMessage("success", "Les données de plus de 30 jours ont été supprimé.");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean reacteur
if (isset($_POST['submit_actions_clear_reacteur'])) {
    clean($link, "reacteur");
    setMessage("success", "Le réacteur a été marqué comme nettoyé.");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean ecumeur
if (isset($_POST['submit_actions_clear_ecumeur'])) {
    clean($link, "ecumeur");
    setMessage("success", "L'écumeur a été marqué comme nettoyé.");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean pompes
if (isset($_POST['submit_actions_clear_pompes'])) {
    clean($link, "pompes");
    setMessage("success", "Les pompes ont été marqué comme nettoyé.");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

// form changement d'eau
if (isset($_POST['submit_eau'])) {
    if (isset($_POST['value']) && is_numeric($_POST['value'])) {
        $sql = 'INSERT INTO `data_changement_eau` ( `value`) VALUES ("' . strval($_POST['value']) . '")';
        $link->query($sql);
    }
    setMessage("success", "Le changement d'eau a été sauvegardé.");
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//delete value changement d'eau
if (isset($_POST['submit_delete_eau'])) {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sql = 'DELETE FROM `data_changement_eau` WHERE `id` LIKE ' . $_POST['id'] . ';';
        setMessage("success", "Le changement d'eau a été supprimé.");
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url'] . "logs"); ///aqua-web
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    foreach ($_POST as $key => $value) {
        setConfig($link, $value, $key);
    }
    setMessage("success", "Les configurations ont été sauvegardé.");
    header('Location: ' . $data['database'][0]['base_url'] . "configuration");
}

// form param d'eau
if (isset($_POST['submit_params'])) {
    foreach ($_POST as $key => $value) {
        setParam($link, $value, str_replace("value_", "", $key));
    }
    setMessage("success", "Les paramètres d'eau ont été sauvegardé.");
    header('Location: ' . $data['database'][0]['base_url']);
}