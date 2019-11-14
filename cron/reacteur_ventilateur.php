<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si on doit allumer l'éclairage
    if (getStatus($link, 'reacteur_ventilateur')) {
        //check les horaires

    } else {
        // on éteind

    }


} catch (Exception $e) { }