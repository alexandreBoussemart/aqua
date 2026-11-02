<?php

require __DIR__ . '/../../app/code/helper/app.php';

$labels = [
    ECUMEUR => "L'écumeur",
    REACTEUR => "Le réacteur",
    OSMOLATEUR => "L'osmolateur",
];

// mise en pause / reprise d'un équipement
foreach ($labels as $type => $label) {
    if (isset($_POST['timer_' . $type])) {
        $date = insertTimer($link, $type);
        $message = $label . " a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
        setMessage("success", $message);
        sendMail($data, $transport, $message, $message, $link);
        redirect($data);
    }

    if (isset($_POST['remove_timer_' . $type])) {
        removeTimer($link, $type);
        $message = $label . " n'est plus en pause.";
        setMessage("success", $message);
        sendMail($data, $transport, $message, $message, $link);
        redirect($data);
    }
}

// changement d'eau : pause osmolateur + réacteur + écumeur
if (isset($_POST['timer_changement_eau'])) {
    $finalMessage = [];
    foreach ([OSMOLATEUR, REACTEUR, ECUMEUR] as $type) {
        $date = insertTimer($link, $type);
        $message = $labels[$type] . " a été mis en pause jusqu'à " . getFormattedHours($date, $link) . ".";
        $finalMessage[] = $message;
        setMessage("success", $message);
    }

    sendMail($data, $transport, "Timer changement d'eau activé.", implode("<br>", $finalMessage), $link);
}

redirect($data);
