<?php

require __DIR__ . '/../../app/code/helper/app.php';

//form action timer ecumeur
if (isset($_POST['timer_ecumeur'])) {
    $date = insertTimer($link, ECUMEUR);
    $message = "L'écumeur a été mis en pause jusqu'à " . getFormattedHours($date) . ".";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}

//form action remove timer ecumeur
if (isset($_POST['remove_timer_ecumeur'])) {
    removeTimer($link, ECUMEUR);
    $message = "L'écumeur n'est plus en pause.";
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    header('Location: ' . $data['database'][0]['base_url']); ///aqua-web
}
