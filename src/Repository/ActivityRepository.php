<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/** Table last_activity : battement de cœur de chaque cron d'équipement. */
final class ActivityRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function touch(string $controle): void
    {
        $this->db->executeStatement('UPDATE `last_activity` SET `created_at` = now() WHERE `value` = ?', [$controle]);
    }

    public function existsBetween(string $controle, \DateTimeInterface $from, \DateTimeInterface $to): bool
    {
        return (bool)$this->db->fetchOne(
            'SELECT 1 FROM `last_activity` WHERE `value` = ? AND `created_at` BETWEEN ? AND ? LIMIT 1',
            [$controle, $from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]
        );
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `last_activity` ORDER BY `id`');
    }
}
