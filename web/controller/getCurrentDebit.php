<?php

require __DIR__ . '/../../app/code/helper/app.php';

// form statut
exec("python " . __DIR__ . "/../../scripts/reacteur_current.py 2>&1", $output, $return_var);
if (isset($output[0]) && $output[0]) {
    echo implode(" -- ", $output);
}