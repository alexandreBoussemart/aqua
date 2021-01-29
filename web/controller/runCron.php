<?php

require __DIR__ . '/../../app/code/helper/functions.php';

$file = $_POST['file'];

exec("php " . $file . " 2>&1", $output, $return_var);
if (isset($output[0]) && $output[0]) {
    echo implode(" -- ", $output);

    return;
}

echo "Run ok";