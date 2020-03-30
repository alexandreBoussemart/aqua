<?php

/**
 * Tous les jours à 19h
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getStatus($link, 'cron_rappel')) {
        exit;
    }

    $body = "";
    if ($rappel[date('D')]) {
        $body = $rappel[date('D')];
    }

    // on envoie le mail avec les rappels du jour
    sendMail($data, $transport, "Rappel - ajout à faire", $body, $link);

    //si pas de changement d'eau depuis plus de 15 jours on envoie un mail de rappel
    checkChangementEau($data, $transport, $link);

    //si pas nettoyé le reacteur depuis plus de 15 jours on envoie un mail de rappel
    checkCleanReacteur($data, $transport, $link);

    //si pas nettoyé le écumeur depuis plus de 30 jours on envoie un mail de rappel
    checkCleanEcumeur($data, $transport, $link);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Ca depuis plus de 6 jours !";
    $subject = "Rappel - faire une mesure du Ca";
    checkParamEau($data, $transport, $link, 'ca', $message, $subject);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Mg depuis plus de 6 jours !";
    $subject = "Rappel - faire une mesure du Mg";
    checkParamEau($data, $transport, $link, 'mg', $message, $subject);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Kh depuis plus de 6 jours !";
    $subject = "Rappel - faire une mesure du Kh";
    checkParamEau($data, $transport, $link, 'kh', $message, $subject);

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de la densité depuis plus de 6 jours !";
    $subject = "Rappel - faire une mesure de la densité";
    checkParamEau($data, $transport, $link, 'densite', $message, $subject);

    exit;

} catch (Exception $e) {
    // on envoie le mail
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Cron erreur - ERREUR - ", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

