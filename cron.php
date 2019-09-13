<?php

require_once 'vendor/autoload.php';

$strJsonFileContents = file_get_contents("config.json");
$data = json_decode($strJsonFileContents, true);

$transport = new Swift_SmtpTransport($data['gmail'][0]['server'], $data['gmail'][0]['port_php'], 'ssl');
$transport->setUsername($data['gmail'][0]['mail'])->setPassword($data['gmail'][0]['password']);

$mailer = new Swift_Mailer($transport);

$message = new Swift_Message('Test message');
$message
   ->setFrom([$data['gmail'][0]['mail'] => $data['gmail'][0]['name']])
   ->setTo([$data['mail_to']])
   ->setSubject('Test message')
   ->setBody('Test Message', 'text/html');

$result = $mailer->send($message);