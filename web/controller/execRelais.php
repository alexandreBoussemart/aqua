<?php

require __DIR__ . '/../../app/code/Helper/functions.php';

if (isset($_POST['relais_ventilateur_boitier_on'])) {
    exec("python " . __DIR__ . "/../../scripts/refroidissement/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_ventilateur_boitier_off'])) {
    exec("python " . __DIR__ . "/../../scripts/refroidissement/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_ventilateur_aquarium_on'])) {
    exec("python " . __DIR__ . "/../../scripts/aquarium_ventilateur/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_ventilateur_aquarium_off'])) {
    exec("python " . __DIR__ . "/../../scripts/aquarium_ventilateur/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_ecumeur_on'])) {
    exec("python " . __DIR__ . "/../../scripts/ecumeur/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_ecumeur_off'])) {
    exec("python " . __DIR__ . "/../../scripts/ecumeur/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_osmolateur_on'])) {
    exec("python " . __DIR__ . "/../../scripts/osmolateur/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_osmolateur_off'])) {
    exec("python " . __DIR__ . "/../../scripts/osmolateur/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_pompe_osmolateur_on'])) {
    exec("python " . __DIR__ . "/../../scripts/pompe_osmolateur/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_pompe_osmolateur_off'])) {
    exec("python " . __DIR__ . "/../../scripts/pompe_osmolateur/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_reacteur_eclairage_on'])) {
    exec("python " . __DIR__ . "/../../scripts/reacteur_eclairage/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_reacteur_eclairage_off'])) {
    exec("python " . __DIR__ . "/../../scripts/reacteur_eclairage/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_reacteur_ventilateur_on'])) {
    exec("python " . __DIR__ . "/../../scripts/reacteur_ventilateur/on.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}

if (isset($_POST['relais_reacteur_ventilateur_off'])) {
    exec("python " . __DIR__ . "/../../scripts/reacteur_ventilateur/off.py 2>&1", $output, $return_var);
    if (isset($output[0]) && $output[0]) {
        setMessage("success", implode(" -- ",$output));
    }
    header('Location: ' . $data['database'][0]['base_url'] . "relais"); ///aqua-web
}
