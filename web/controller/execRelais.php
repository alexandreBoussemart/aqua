<?php

require __DIR__ . '/../../app/code/helper/app.php';

// bouton du formulaire => script Python (scripts/<dossier>/<on|off>.py)
$relais = [
    'ventilateur_aquarium' => 'aquarium_ventilateur',
    'ecumeur' => 'ecumeur',
    'pompe_osmolateur' => 'pompe_osmolateur',
    'reacteur_eclairage' => 'reacteur_eclairage',
    'reacteur_ventilateur' => 'reacteur_ventilateur',
];

foreach ($relais as $name => $folder) {
    foreach (['on', 'off'] as $action) {
        if (!isset($_POST["relais_{$name}_{$action}"])) {
            continue;
        }

        $script = __DIR__ . "/../../scripts/{$folder}/{$action}.py";
        exec("python3 " . escapeshellarg($script) . " 2>&1", $output, $return_var);
        if (isset($output[0]) && $output[0]) {
            setMessage("success", implode(" -- ", $output));
        }
        redirect($data, "relais");
    }
}

redirect($data, "relais");
