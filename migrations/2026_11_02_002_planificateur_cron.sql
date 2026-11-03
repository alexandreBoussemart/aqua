-- Planificateur unique : planification de chaque commande app:cron:* modifiable depuis la page /crons
CREATE TABLE IF NOT EXISTS `cron_task` (
    `name` varchar(100) NOT NULL PRIMARY KEY,
    `expression` varchar(100) NOT NULL DEFAULT '* * * * *',
    `enabled` tinyint(1) NOT NULL DEFAULT 1,
    `last_started_at` datetime DEFAULT NULL,
    `last_finished_at` datetime DEFAULT NULL,
    `last_duration` float DEFAULT NULL,
    `last_exit_code` int DEFAULT NULL,
    `last_output` text DEFAULT NULL
) DEFAULT CHARSET=utf8mb4;

-- planification reprise de l'ancienne crontab du Raspberry
INSERT IGNORE INTO `cron_task` (`name`, `expression`) VALUES
    ('app:cron:check-controle', '* * * * *'),
    ('app:cron:mail', '* * * * *'),
    ('app:cron:reacteur-gestion', '* * * * *'),
    ('app:cron:temperature', '* * * * *'),
    ('app:cron:rappel', '0 19 * * *'),
    ('app:cron:bailling', '* * * * *'),
    ('app:cron:ecumeur', '* * * * *'),
    ('app:cron:osmolateur', '* * * * *'),
    ('app:cron:temperature-air', '* * * * *'),
    ('app:cron:temperature-rpi', '* * * * *'),
    ('app:cron:timer', '* * * * *'),
    ('app:cron:dump', '0 0 * * *');
