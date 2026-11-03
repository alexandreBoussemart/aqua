<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Table core_config_status (interrupteurs on/off).
 * Chaque statut actif est aussi matérialisé par un fichier var/status/<nom> : lecture très rapide
 * pour les crons qui bouclent toutes les 0,5 s.
 */
final class StatusRepository
{
    private readonly string $dir;

    public function __construct(private readonly Connection $db, string $varDir)
    {
        $this->dir = $varDir . '/status';
    }

    public function isEnabled(string $name): bool
    {
        return preg_match('/^[a-z0-9_]+$/', $name) === 1 && is_file($this->dir . '/' . $name);
    }

    public function set(string $name, bool $enabled): void
    {
        if (preg_match('/^[a-z0-9_]+$/', $name) !== 1) {
            throw new \InvalidArgumentException('Statut invalide : ' . $name);
        }
        $this->db->executeStatement('UPDATE `core_config_status` SET `value` = ? WHERE `name` = ?', [(int)$enabled, $name]);

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0775, true);
        }
        $file = $this->dir . '/' . $name;
        if ($enabled) {
            touch($file);
        } elseif (is_file($file)) {
            unlink($file);
        }
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `core_config_status` ORDER BY `groupe`, `id`');
    }

    /** @return list<string> */
    public function names(): array
    {
        return $this->db->fetchFirstColumn('SELECT `name` FROM `core_config_status`');
    }

    /** @param list<string> $names @return list<array<string, mixed>> */
    public function findByNames(array $names): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT * FROM `core_config_status` WHERE `name` IN (?) ORDER BY `groupe`, `id`',
            [$names],
            [\Doctrine\DBAL\ArrayParameterType::STRING]
        );
    }

    /** Recrée les fichiers var/status depuis la BDD et supprime ceux des statuts disparus (installation / migration). */
    public function syncFiles(): void
    {
        $names = [];
        foreach ($this->all() as $status) {
            $this->set($status['name'], (bool)$status['value']);
            $names[] = $status['name'];
        }
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            if (!in_array(basename($file), $names, true)) {
                unlink($file);
            }
        }
    }
}
