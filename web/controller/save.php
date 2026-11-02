<?php

require __DIR__ . '/../../app/code/helper/app.php';

/**
 * Message flash + mail de notification, puis redirection.
 */
function done(array $data, $transport, $link, string $message, string $page = ''): void
{
    setMessage("success", $message);
    sendMail($data, $transport, $message, $message, $link);
    redirect($data, $page);
}

// actions de maintenance (page configuration)
if (isset($_POST['submit_actions_clear'])) {
    clear($link);
    done($data, $transport, $link, "Les données de plus de 30 jours ont été supprimées.", "configuration");
}

$cleanActions = [
    'submit_actions_clear_reacteur' => ['reacteur', "Le réacteur a été marqué comme nettoyé."],
    'submit_actions_clear_ecumeur' => ['ecumeur', "L'écumeur a été marqué comme nettoyé."],
    'submit_actions_clear_pompes' => ['pompes', "Les pompes ont été marquées comme nettoyées."],
];
foreach ($cleanActions as $field => [$type, $message]) {
    if (isset($_POST[$field])) {
        clean($link, $type);
        done($data, $transport, $link, $message, "configuration");
    }
}

// form changement d'eau
if (isset($_POST['submit_eau'])) {
    if (isset($_POST['value']) && is_numeric($_POST['value'])) {
        dbQuery($link, 'INSERT INTO ' . TABLE_DATA_CHANGEMENT_EAU . ' (`value`) VALUES (?)', [(string)$_POST['value']]);
    }
    done($data, $transport, $link, "Le changement d'eau a été sauvegardé.");
}

// suppression d'un changement d'eau / d'une dépense
$deleteActions = [
    'submit_delete_eau' => [TABLE_DATA_CHANGEMENT_EAU, "Le changement d'eau a été supprimé.", "logs"],
    'submit_delete_budget' => [TABLE_DATA_DEPENSE, "La dépense a été supprimée.", "budget"],
];
foreach ($deleteActions as $field => [$table, $message, $page]) {
    if (isset($_POST[$field])) {
        if (isset($_POST['id']) && ctype_digit((string)$_POST['id'])) {
            dbQuery($link, 'DELETE FROM ' . $table . ' WHERE `id` = ?', [(int)$_POST['id']]);
            done($data, $transport, $link, $message, $page);
        }
        redirect($data, $page);
    }
}

// form configuration
if (isset($_POST['submit_configuration'])) {
    unset($_POST['submit_configuration']);
    foreach ($_POST as $key => $value) {
        setConfig($link, $value, $key);
    }
    done($data, $transport, $link, "Les configurations ont été sauvegardées.", "configuration");
}

// form param d'eau
if (isset($_POST['submit_params'])) {
    unset($_POST['submit_params']);
    foreach ($_POST as $key => $value) {
        setParam($link, $value, str_replace("value_", "", $key));
    }
    done($data, $transport, $link, "Les paramètres d'eau ont été sauvegardés.", "analyse");
}

// form dépense
if (isset($_POST['submit_budget'])) {
    $price = (float)str_replace(",", ".", (string)($_POST['value'] ?? ''));
    $date = false;
    foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format) {
        $date = DateTime::createFromFormat('!' . $format, trim((string)($_POST['date'] ?? '')));
        if ($date) {
            break;
        }
    }

    if (!$date || !isset($_POST['comment'])) {
        setMessage("error", "Dépense non enregistrée : date ou commentaire invalide.");
        redirect($data, "budget");
    }

    dbQuery(
        $link,
        'INSERT INTO ' . TABLE_DATA_DEPENSE . ' (`comment`, `value`, `created_at`) VALUES (?, ?, ?)',
        [(string)$_POST['comment'], $price, $date->format('Y-m-d')]
    );
    done($data, $transport, $link, "La dépense a été sauvegardée.", "budget");
}

redirect($data);
