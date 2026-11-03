<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/** Table core_config_value : paramètres modifiables depuis la page Configuration. */
final class ConfigRepository
{
    /** Bornes des réglages numériques sensibles : nom => [min, max, valeur par défaut si absente ou invalide] */
    public const BOUNDS = [
        'temps_coupure_pompe_osmolateur' => [5, 600, 25],
        'temps_mini_pompe_osmolateur' => [0, 60, 10],
    ];

    public function __construct(private readonly Connection $db)
    {
    }

    public function getRaw(string $name): ?string
    {
        $value = $this->db->fetchOne('SELECT `value` FROM `core_config_value` WHERE `name` = ?', [$name]);

        return $value === false ? null : (string)$value;
    }

    public function getInt(string $name, int $default = 0): int
    {
        $value = $this->getRaw($name);

        return $value === null ? $default : (int)$value;
    }

    public function getFloat(string $name, float $default = 0.0): float
    {
        $value = $this->getRaw($name);

        return $value === null ? $default : (float)$value;
    }

    /** Valeur entière bornée (ConfigRepository::BOUNDS) : défaut si absente ou hors bornes, pour qu'une sécurité ne soit jamais désactivée. */
    public function getBounded(string $name): int
    {
        [$min, $max, $default] = self::BOUNDS[$name];
        $value = $this->getRaw($name);
        if ($value === null || !is_numeric($value) || (int)$value < $min || (int)$value > $max) {
            return $default;
        }

        return (int)$value;
    }

    public function set(string $name, string $value): void
    {
        $this->db->executeStatement('UPDATE `core_config_value` SET `value` = ? WHERE `name` = ?', [$value, $name]);
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `core_config_value` ORDER BY `groupe`, `id`');
    }
}
