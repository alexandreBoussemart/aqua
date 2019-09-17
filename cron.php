<?php

require_once 'vendor/autoload.php';

try {
    $strJsonFileContents = file_get_contents(__DIR__ . "/config.json");
    $data = json_decode($strJsonFileContents, true);

    $transport = new Swift_SmtpTransport($data['gmail'][0]['server'], $data['gmail'][0]['port_php'], 'ssl');
    $transport->setUsername($data['gmail'][0]['mail'])->setPassword($data['gmail'][0]['password']);

    $link = new mysqli($data['database'][0]['host'], $data['database'][0]['user'], $data['database'][0]['passwd'], $data['database'][0]['database']);
    if ($link->connect_error) {
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message("Cron - ERREUR");
        $message
            ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
            ->setTo([$data['mail_to']])
            ->setSubject("Cron - ERREUR")
            ->setBody('Connect Error (' . $link->connect_errno . ') ' . $link->connect_error, 'text/html');

        $result = $mailer->send($message);
    }

    $array_verif = ['controle_bailling', 'controle_ecumeur', 'controle_osmolateur', 'controle_reacteur', 'controle_temperature'];
    $message_body = [
        'controle_bailling' => 'Cron - Erreur script bailling',
        'controle_ecumeur' => 'Cron - Erreur script écumeur',
        'controle_osmolateur' => 'Cron - Erreur script osmolateur',
        'controle_reacteur' => 'Cron - Erreur script réacteur',
        'controle_temperature' => 'Cron - Erreur script température'
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
        //on prend les lignes avec mail send datant de 30 minutes
        $sql = "SELECT * FROM `controle` where `mail_send` = 1 and `value` = '" . $verif . "' and `created_at` >= '" . $yesterday_30 . "' and `created_at` <= '" . $today_30 . "' limit 1";
        $controle = mysqli_query($link, $sql);
        $row_send = mysqli_fetch_assoc($controle);

        if ($row_send != NULL) {
            if (array_key_exists($verif, $message_body)) {
                $text = strval($message_body[$verif]);
                $text = str_replace('Erreur', 'RAPPEL Erreur',$text);
                $body = "<p style='color:red;text-transform:uppercase;'>" . $text . "</p>";

                $mailer = new Swift_Mailer($transport);
                $message = new Swift_Message($text);
                $message
                    ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
                    ->setTo([$data['mail_to']])
                    ->setSubject($text)
                    ->setBody($body, 'text/html');

                $result = $mailer->send($message);

                //on set new date a now
                $d = new DateTime();
                $new_date = $d->format('Y-m-d H:i:s');
                $sql = "UPDATE `controle` SET `created_at` = '".$new_date."' WHERE `controle`.`value` = '".$verif."';";
                $link->query($sql);
            }
        }

        /*------------------------------------------------------------------------------------------------------------*/

        //on prend que les lignes avec mail datant de -1 minutes, et on verifie que pas de mail send avant envoie
        $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
        $controle = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($controle);

        if ($row == NULL) {
            //on fait une deuxième verif au bout de 10 secondes
            sleep(10);
            $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' limit 1";
            $controle = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($controle);

            if ($row == NULL) {
                // on regarde qu'un mail n'a pas été envoyé deja (mail_send a avec le code)
                $sql = "SELECT * FROM `controle` where `value` = '" . $verif . "' and `mail_send` = 1 limit 1";
                $controle = mysqli_query($link, $sql);
                $row = mysqli_fetch_assoc($controle);

                if ($row == NULL) {
                    $mailer = new Swift_Mailer($transport);

                    if (array_key_exists($verif, $message_body)) {
                        $text = strval($message_body[$verif]);
                        $body = "<p style='color:red;text-transform:uppercase;'>" . $text . "</p>";

                        $message = new Swift_Message($text);
                        $message
                            ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
                            ->setTo([$data['mail_to']])
                            ->setSubject($text)
                            ->setBody($body, 'text/html');

                        $result = $mailer->send($message);

                        //on set mail send à 1
                        $sql = "UPDATE `controle` SET `mail_send` = '1' WHERE `controle`.`value` = '".$verif."';";
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

    if($current == $huit){
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message("Cron - contrôle 8h - OK");
        $message
            ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
            ->setTo([$data['mail_to']])
            ->setSubject("Cron - contrôle 8h - OK")
            ->setBody("<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>", 'text/html');

        $result = $mailer->send($message);
    }

} catch (Exception $e) {
    $mailer = new Swift_Mailer($transport);
    $message = new Swift_Message("Cron - ERREUR");
    $message
        ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
        ->setTo([$data['mail_to']])
        ->setSubject("Cron - ERREUR")
        ->setBody($e->getMessage(), 'text/html');

    $result = $mailer->send($message);
}



