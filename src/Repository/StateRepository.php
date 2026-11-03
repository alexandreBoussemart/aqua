<?php

namespace App\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Table core_config_state : état courant de chaque équipement (state_X, erreur, message).
 * Le fichier var/state/<path>-<value> sert de cache : l'état n'est réécrit (et logué) que s'il change.
 * Les scripts Python utilisent la même convention (scripts/functions.py::setcompletestate).
 */
final class StateRepository
{
    private readonly string $dir;

    public function __construct(private readonly Connection $db, private readonly LogRepository $logs, string $varDir)
    {
        $this->dir = $varDir . '/state';
    }

    /** @return bool true si l'état a changé */
    public function set(string $path, string $value, bool $error, string $message, bool $excludeCheck = false, bool $forceLog = false): bool
    {
        $file = $this->dir . '/' . $path . '-' . $value;

        if (!is_file($file)) {
            $this->db->executeStatement(
                'UPDATE `core_config_state` SET `value` = ?, `error` = ?, `message` = ?, `created_at` = now(), `mail_send` = 0, `exclude_check` = ? WHERE `path` = ?',
                [$value, (int)$error, $message, (int)$excludeCheck, $path]
            );
            $this->logs->log($message);

            if (!is_dir($this->dir)) {
                mkdir($this->dir, 0775, true);
            }
            foreach (glob($this->dir . '/' . $path . '-*') ?: [] as $old) {
                @unlink($old);
            }
            touch($file);

            return true;
        }

        if ($forceLog) {
            $this->logs->log($message);
        }

        return false;
    }

    /** @return array<string, mixed>|null */
    public function find(string $path): ?array
    {
        return $this->db->fetchAssociative('SELECT * FROM `core_config_state` WHERE `path` = ?', [$path]) ?: null;
    }

    /** @param list<string> $values */
    public function isIn(string $path, array $values): bool
    {
        return (bool)$this->db->fetchOne(
            'SELECT 1 FROM `core_config_state` WHERE `path` = ? AND `value` IN (?) LIMIT 1',
            [$path, $values],
            [\Doctrine\DBAL\ParameterType::STRING, ArrayParameterType::STRING]
        );
    }

    /** @param list<string> $paths @return list<array<string, mixed>> */
    public function findByPaths(array $paths): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT * FROM `core_config_state` WHERE `path` IN (?) ORDER BY `id`',
            [$paths],
            [ArrayParameterType::STRING]
        );
    }

    /** États des contrôles de crons + date de dernière activité. @return list<array<string, mixed>> */
    public function controles(): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT s.`error`, s.`label`, la.`created_at` AS `date`
             FROM `core_config_state` s
             INNER JOIN `last_activity` la ON la.`value` = s.`path`
             WHERE s.`path` LIKE 'controle\\_%'
             ORDER BY s.`id`"
        );
    }

    /** États à notifier par mail (nouveaux changements). @return list<array<string, mixed>> */
    public function pendingNotifications(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `core_config_state` WHERE `exclude_check` = 0 AND `mail_send` = 0');
    }

    /** Erreurs toujours actives depuis avant $before (mail de rappel). @return list<array<string, mixed>> */
    public function errorsSince(\DateTimeInterface $before): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT * FROM `core_config_state` WHERE `exclude_check` = 0 AND `error` = 1 AND `created_at` < ?',
            [$before->format('Y-m-d H:i:00')]
        );
    }

    public function markNotified(int $id): void
    {
        $this->db->executeStatement('UPDATE `core_config_state` SET `mail_send` = 1, `created_at` = now() WHERE `id` = ?', [$id]);
    }

    public function touch(int $id): void
    {
        $this->db->executeStatement('UPDATE `core_config_state` SET `created_at` = now() WHERE `id` = ?', [$id]);
    }
}
