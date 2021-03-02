<?php

require __DIR__ . '/../../app/code/helper/app.php';

//form action timer ecumeur
if (isset($_POST['timer_ecumeur'])) {
    $date = insertTimer($link, ECUMEUR);
    $message = "L'écumeur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

//form action remove timer ecumeur
if (isset($_POST['remove_timer_ecumeur'])) {
    removeTimer($link, ECUMEUR);
    $message = "L'écumeur n'est plus en pause.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

//form action timer ecumeur
if (isset($_POST['timer_reacteur'])) {
    $date = insertTimer($link, REACTEUR);
    $message = "Le réacteur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

//form action remove timer ecumeur
if (isset($_POST['remove_timer_reacteur'])) {
    removeTimer($link, REACTEUR);
    $message = "Le réacteur n'est plus en pause.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

//form action timer osmolateur
if (isset($_POST['timer_osmolateur'])) {
    $date = insertTimer($link, OSMOLATEUR);
    $message = "L'osmolateur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

//form action remove timer osmolateur
if (isset($_POST['remove_timer_osmolateur'])) {
    removeTimer($link, OSMOLATEUR);
    $message = "L'osmolateur n'est plus en pause.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}

if (isset($_POST['timer_changement_eau'])) {
    $finalMessage = [];

    $date = insertTimer($link, OSMOLATEUR);
    $message = "L'osmolateur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    $finalMessage[] = $message;
    setMessage("success", $message);

    $date = insertTimer($link, REACTEUR);
    $message = "Le réacteur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    $finalMessage[] = $message;
    setMessage("success", $message);

    $date = insertTimer($link, ECUMEUR);
    $message = "L'écumeur a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
    $finalMessage[] = $message;
    setMessage("success", $message);

    $message = "Timer changement d'eau activé.";
    sendMail($data, $transport, $message, implode("<br>", $finalMessage), $link);
    header('Location: ' . $data['database'][0]['base_url']); //aqua-web
}