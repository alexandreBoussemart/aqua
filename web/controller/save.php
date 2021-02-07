<?php

require __DIR__ . '/../../app/code/helper/app.php';

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
        $sql = 'INSERT INTO '.TABLE_DATA_CHANGEMENT_EAU.' ( `value`) VALUES ("' . strval($_POST['value']) . '")';
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
        $sql = 'DELETE FROM '.TABLE_DATA_CHANGEMENT_EAU.' WHERE `id` LIKE ' . $_POST['id'] . ';';
        $message = "Le changement d'eau a été supprimé.";
        setMessage("success", $message);
        sendMail($data, $transport, $message, $message, $link);
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url'] . "logs"); ///aqua-web
}

//delete value budget
if (isset($_POST['submit_delete_budget'])) {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sql = 'DELETE FROM '.TABLE_DATA_DEPENSE.' WHERE `id` LIKE ' . $_POST['id'] . ';';
        $message = "La dépense a été supprimé.";
        setMessage("success", $message);
        sendMail($data, $transport, $message, $message, $link);
        $link->query($sql);
    }

    header('Location: ' . $data['database'][0]['base_url'] . "budget"); ///aqua-web
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
    header('Location: ' . $data['database'][0]['base_url'] . "analyse");
}

// form dépense
if (isset($_POST['submit_budget']) && isset($_POST['value'])) {
    $price = str_replace(",", ".", $_POST['value']);
    $price = (float)$price;

    if (isset($_POST['value']) && is_float($price) && isset($_POST['comment']) && isset($_POST['date'])) {
        $date = $_POST['date'];
        $sql = 'INSERT INTO '.TABLE_DATA_DEPENSE.' ( `comment`,`value`,`created_at`) VALUES ("' . strval($_POST['comment']) . '",' . $price . ',"' . $date . '")';
        $link->query($sql);
    }

    $message = "Le dépense a été sauvegardé.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url'] . "budget"); ///aqua-web
}
