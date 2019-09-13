<?php

require_once __DIR__.'/lib/swift_required.php';

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
  ->setUsername('raspberrypi.recifal@gmail.com')
  ->setPassword('raspberrypi1!');

$mailer = Swift_Mailer::newInstance($transport);

$message = Swift_Message::newInstance('Test Subject')
  ->setFrom(array('alexandre.boussemart94@gmail.com' => 'ABC'))
  ->setTo(array('alexandre.boussemart94@gmail.com'))
  ->setBody('This is a test mail.');

$result = $mailer->send($message);