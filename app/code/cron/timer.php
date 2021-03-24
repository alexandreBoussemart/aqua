<?php

/**
 * Toutes les minutes
 */

require __DIR__ . '/../helper/app.php';

try {
    // désactive toutes les crons
    if (getStatus($link, DISABLE_ALL_CRON)) {
        exit;
    }

    //heure d'execution max
    $date = new DateTime();
    $date = $date->format('Y-m-d H:i:s');

    try {
        $sql = "# noinspection SqlNoDataSourceInspectionForFile 
            SELECT `type`
            FROM " . TABLE_TIMER .
            "WHERE `off_until` <= '" . $date . "'";
        logInFile($link, "sql.log", $sql);
        $request = mysqli_query($link, $sql);
        $rows = mysqli_fetch_assoc($request);

        if ($rows) {
            foreach ($rows as $type) {
                removeTimer($link, $type);
            }
        }

    } catch (Exception $e) {
        setLog($link, $e->getMessage());

        return '';
    }

    exit;

} catch (Exception $e) {
    try {
        setLog($link, $e->getMessage());
        sendMail($data, $transport, "Erreur script timer.php", $e->getMessage(), $link);
    } catch (Exception $e) {
        setLog($link, $e->getMessage());
    }

    exit;
}

