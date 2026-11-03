<?php

namespace App\Database;

use App\Config\AppConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

final class ConnectionFactory
{
    public function __construct(private readonly AppConfig $config)
    {
    }

    public function create(): Connection
    {
        $db = $this->config->database();

        return DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $db['host'],
            'user' => $db['user'],
            'password' => $db['passwd'],
            'dbname' => $db['database'],
            'charset' => 'utf8mb4',
        ]);
    }
}
