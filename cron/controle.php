<?php

require 'functions.php';

try {
    //check si la cron est activé
    if(!getConfig($link, 'cron')) {
        return false;
    }

    $array_verif = [
        'controle_bailling',
        'controle_ecumeur',
        'controle_osmolateur',
        'controle_reacteur',
        'controle_temperature'
    ];
    $message_body = [
        'controle_bailling'    => 'Cron - Erreur script bailling',
        'controle_ecumeur'     => 'Cron - Erreur script écumeur',
        'controle_osmolateur'  => 'Cron - Erreur script osmolateur',
        'controle_reacteur'    => 'Cron - Erreur script réacteur',
        'controle_temperature' => 'Cron - Erreur script température'
    ];

    $rappel = [
        "Mon" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color: #9B59B6">Bactérie</p><p style="color:#1ABB9C ">Algue</p>',
        "Tue" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
        "Wed" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color:#1ABB9C ">Algue</p>',
        "Thu" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
        "Fri" => '<p style="color: #3a87ad;">Nourriture congelée</p><p style="color: #E74C3C">Nourriture coraux</p><p style="color:#1ABB9C ">Algue</p>',
        "Sat" => '<p style="color: #3a87ad;">Nourriture congelée</p>',
        "Sun" => '<p style="color: #3a87ad;">Nourriture congelée</p>'
    ];

    //date now - 1 minute
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:59');
    $date->modify("-1 minute");
    $yesterday = $date->format('Y-m-d H:i:00');

    //date now -30 minutes
    $date = new DateTime();
    $date->modify("-30 minutes");
    $today_30 = $date->format('Y-m-d H:i:59');

    //date now -31 minutes
    $date = new DateTime();
    $date->modify("-31 minutes");
    $yesterday_30 = $date->format('Y-m-d H:i:00');

    foreach ($array_verif as $verif) {
        //on prend les lignes avec mail send datant de 30 minutes pour mail de rappel
        $sql = "SELECT * FROM `controle` where `mail_send` = 1 and `value` = '" . $verif . "' and `created_at` >= '" . $yesterday_30 . "' and `created_at` <= '" . $today_30 . "' limit 1";
        $controle = mysqli_query($link, $sql);
        $row_send = mysqli_fetch_assoc($controle);

        if ($row_send != null) {
            if (array_key_exists($verif, $message_body)) {
                $text = strval($message_body[$verif]);
                $text = str_replace('Erreur', 'RAPPEL Erreur', $text);
                $body = "<p style='color:red;text-transform:uppercase;'>" . $text . "</p>";

                sendMail($data, $transport, $text, $body);

                //on set new date a now
                $d = new DateTime();
                $new_date = $d->format('Y-m-d H:i:s');
                $sql = "UPDATE `controle` SET `created_at` = '" . $new_date . "' WHERE `controle`.`value` = '" . $verif . "';";
                $link->query($sql);
            }
        }

        /*------------------------------------------------------------------------------------------------------------*/

        //on prend que les lignes avec mail datant de -1 minutes, et on verifie que pas de mail send avant envoie
        $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row == null) {
            //on fait une deuxième verif au bout de 10 secondes
            sleep(10);
            $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
            $controle = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($controle);

            if ($row == null) {
                // on regarde qu'un mail n'a pas été envoyé deja (mail_send a avec le code)
                $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `mail_send` = 1 limit 1";
                $controle = mysqli_query($link, $sql);
                $row = mysqli_fetch_assoc($controle);

                if ($row == null) {
                    if (array_key_exists($verif, $message_body)) {
                        $text = strval($message_body[$verif]);
                        $body = "<p style='color:red;text-transform:uppercase;'>" . $text . "</p>";
                        sendMail($data, $transport, $text, $body);

                        //on set mail send à 1
                        $sql = "UPDATE `controle` SET `mail_send` = '1' WHERE `controle`.`value` = '" . $verif . "';";
                        $link->query($sql);
                    }
                }
            }
        }
    }

    //controle 8h
    $date = new DateTime();
    $current = $date->format('Y-m-d H:i:00');
    $huit = $date->format('Y-m-d 08:00:00');

    if ($current == $huit) {
        sendMail($data, $transport, "Cron - contrôle 8h - OK", "<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>");
    }

    //rappel ajout à 19h
    $date = new DateTime();
    $current = $date->format('Y-m-d H:i:00');
    $dixneuf = $date->format('Y-m-d 19:00:00');
    $body = "";

    if ($current == $dixneuf) {
        if($rappel[date('D')]){
            $body = $rappel[date('D')];
        }

        sendMail($data, $transport, "Rappel - ajout à faire", $body);
    }

} catch (Exception $e) {
    sendMail($data, $transport, "Cron controle - ERREUR", $e->getMessage());
}



