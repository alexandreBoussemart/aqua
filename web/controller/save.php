<?php

require __DIR__ . '/../../app/code/Helper/functions.php';

//form action clear data
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    $message = "Les données de plus de 30 jours ont été supprimé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean reacteur
if (isset($_POST['submit_actions_clear_reacteur'])) {
    clean($link, "reacteur");
    $message = "Le réacteur a été marqué comme nettoyé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean ecumeur
if (isset($_POST['submit_actions_clear_ecumeur'])) {
    clean($link, "ecumeur");
    $message = "L'écumeur a été marqué comme nettoyé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

//form action clean pompes
if (isset($_POST['submit_actions_clear_pompes'])) {
    clean($link, "pompes");
    $message = "Les pompes ont été marqué comme nettoyé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration"); ///aqua-web
}

// form changement d'eau
if (isset($_POST['submit_eau'])) {
    if (isset($_POST['value']) && is_numeric($_POST['value'])) {
        $sql = 'INSERT INTO `data_changement_eau` ( `value`) VALUES ("' . strval($_POST['value']) . '")';
        $link->query($sql);
    }
    $message = "Le changement d'eau a été sauvegardé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//delete value changement d'eau
if (isset($_POST['submit_delete_eau'])) {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sql = 'DELETE FROM `data_changement_eau` WHERE `id` LIKE ' . $_POST['id'] . ';';
        $message =  "Le changement d'eau a été supprimé.";
        setMessage("success",$message);
        sendMail($data, $transport, $message, $message, $link);
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url'] . "logs"); ///aqua-web
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    unset($_POST['submit_configuration']);
    foreach ($_POST as $key => $value) {
        setConfig($link, $value, $key);
    }
    $message = "Les configurations ont été sauvegardé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "configuration");
}

// form param d'eau
if (isset($_POST['submit_params'])) {
    unset($_POST['submit_params']);
    foreach ($_POST as $key => $value) {
        setParam($link, $value, str_replace("value_", "", $key));
    }
    $message = "Les paramètres d'eau ont été sauvegardé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']);
}