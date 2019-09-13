<?php

require_once 'vendor/autoload.php';

$transport = new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
$transport->setUsername('raspberrypi.recifal@gmail.com')->setPassword('Trex450pro');

$mailer = new Swift_Mailer($transport);

$message = new Swift_Message('Test message');
$message
   ->setFrom(['raspberrypi.recifal@gmail.com' => 'raspberrypi.recifal'])
   ->setTo(['alexandre.boussemart94@gmail.com' => 'Recipient'])
   ->setSubject('Test message')
   ->setBody('Test Message', 'text/html');

$result = $mailer->send($message);