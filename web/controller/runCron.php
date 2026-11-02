<?php

require __DIR__ . '/../../app/code/helper/app.php';

// Seuls les crons présents dans app/code/cron/ peuvent être lancés (nom de fichier, pas de chemin libre)
$requested = basename((string)($_POST['file'] ?? ''));
$cron = null;
foreach (getCrons() as $candidate) {
    if ($candidate['type'] === $requested) {
        $cron = $candidate;
        break;
    }
}

if ($cron === null) {
    http_response_code(400);
    echo "Cron inconnu";

    return;
}

// sous Apache (mod_php), PHP_BINARY est vide : on prend le binaire CLI
$php = is_executable(PHP_BINDIR . '/php') ? PHP_BINDIR . '/php' : 'php';
exec(escapeshellarg($php) . " " . escapeshellarg($cron['run']) . " 2>&1", $output, $return_var);
if (isset($output[0]) && $output[0]) {
    echo implode(" -- ", $output);

    return;
}

echo "Run ok";
