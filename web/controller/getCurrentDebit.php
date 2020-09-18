<?php

require __DIR__ . '/../../app/code/Helper/functions.php';

// form statut
exec("python " . __DIR__ . "/../../scripts/reacteur_current.py 2>&1", $output, $return_var);
if($output[0]){
    echo $output[0];
}