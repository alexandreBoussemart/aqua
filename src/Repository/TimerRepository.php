<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/** Table timer : mise en pause temporaire d'un équipement. */
final class TimerRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function pauseUntil(string $type, \DateTimeInterface $until): void
    {
        $this->db->executeStatement(
            'INSERT INTO `timer` (`type`, `off_until`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `off_until` = VALUES(`off_until`)',
            [$type, $until->format('Y-m-d H:i:s')]
        );
    }

    public function get(string $type): ?\DateTimeImmutable
    {
        $value = $this->db->fetchOne('SELECT `off_until` FROM `timer` WHERE `type` = ?', [$type]);

        return $value ? new \DateTimeImmutable($value) : null;
    }

    public function has(string $type): bool
    {
        return $this->get($type) !== null;
    }

    public function remove(string $type): void
    {
        $this->db->executeStatement('DELETE FROM `timer` WHERE `type` = ?', [$type]);
    }

    /** Supprime les pauses expirées. @return int nombre supprimé */
    public function removeExpired(): int
    {
        return (int)$this->db->executeStatement('DELETE FROM `timer` WHERE `off_until` <= now()');
    }
}
