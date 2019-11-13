<?php

/**
 * Toutes les minutes
 */

require 'helper/functions.php';

try {
    //check si la cron est activé
    if (!getConfig($link, 'cron_mail')) {
        return false;
    }



} catch (Exception $e) { }

