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
    $message = "Pas de changement d'eau depuis XX jours !";
    $subject = "Rappel - faire un changement d'eau";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_changement_eau', 'type' => ''], $message, $subject, "check_changement_eau");

    //si pas nettoyé le reacteur depuis plus de 15 jours on envoie un mail de rappel
    $message = "Le réacteur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer le reacteur";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_reacteur', 'type' => ''], $message, $subject, "check_clean_reacteur");

    //si pas nettoyé le écumeur depuis plus de 30 jours on envoie un mail de rappel
    $message = "L'écumeur n'a pas été nettoyé depuis XX jours !";
    $subject = "Rappel - nettoyer l'écumeur";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_ecumeur', 'type' => ''], $message, $subject, "check_clean_ecumeur");

    //si pas nettoyé les pompes depuis plus de 90 jours on envoie un mail de rappel
    $message = "Les pompes n'ont pas été nettoyé depuis depuis XX jours !";
    $subject = "Rappel - nettoyer les pompes";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_clean_pompes', 'type' => ''], $message, $subject, "check_clean_pompes");

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Ca depuis XX jours !";
    $subject = "Rappel - faire une mesure du Ca";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'ca'], $message, $subject, "check_analyse_eau");

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Mg depuis XX jours !";
    $subject = "Rappel - faire une mesure du Mg";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'mg'], $message, $subject, "check_analyse_eau");

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure du Kh depuis XX jours !";
    $subject = "Rappel - faire une mesure du Kh";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'kh'], $message, $subject, "check_analyse_eau");

    //si pas de mesure depuis plus d'1 semaine
    $message = "Pas de mesure de la densité depuis XX jours !";
    $subject = "Rappel - faire une mesure de la densité";
    checkLastTimeCheck($data, $transport, $link, ['table' => 'data_parametres_eau', 'type' => 'densite'], $message, $subject, "check_analyse_eau");

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

