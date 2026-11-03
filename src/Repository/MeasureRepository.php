<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Mesures horodatées (températures, paramètres d'eau, changements d'eau, nettoyages).
 * Les noms de tables passent par une liste blanche (on ne peut pas les lier en paramètre SQL).
 */
final class MeasureRepository
{
    public const WATER_TEMPERATURE = 'data_temperature_eau';
    public const AIR_TEMPERATURE = 'data_temperature_air';
    public const CPU_TEMPERATURE = 'data_temperature_rpi';
    public const WATER_PARAMS = 'data_parametres_eau';
    public const WATER_CHANGES = 'data_changement_eau';
    public const CLEAN_PREFIX = 'data_clean_';

    public const CLEANABLE = ['refuge', 'ecumeur', 'pompes'];

    /** tables purgées par l'action « Vider BDD > 30 jours » */
    public const PURGEABLE = [self::WATER_TEMPERATURE, self::AIR_TEMPERATURE, self::CPU_TEMPERATURE, 'log', 'log_mail'];

    private const TABLES = [
        self::WATER_TEMPERATURE, self::AIR_TEMPERATURE, self::CPU_TEMPERATURE,
        self::WATER_PARAMS, self::WATER_CHANGES, 'data_clean_refuge', 'data_clean_ecumeur', 'data_clean_pompes',
        'log', 'log_mail',
    ];

    public function __construct(private readonly Connection $db)
    {
    }

    private function table(string $table): string
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new \InvalidArgumentException('Table inconnue : ' . $table);
        }

        return '`' . $table . '`';
    }

    public function add(string $table, float|string $value): void
    {
        $this->db->executeStatement('INSERT INTO ' . $this->table($table) . ' (`value`) VALUES (?)', [(string)$value]);
    }

    /** @return list<array<string, mixed>> lignes {created_at, value} */
    public function between(string $table, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT `created_at`, `value` FROM ' . $this->table($table) . ' WHERE `created_at` BETWEEN ? AND ? ORDER BY `created_at`',
            [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]
        );
    }

    /**
     * Dernière ligne (filtrée par type pour data_parametres_eau). @return array<string, mixed>|null
     * @return array<string, mixed>|null
     */
    public function last(string $table, ?string $type = null, int $offset = 0): ?array
    {
        $sql = 'SELECT * FROM ' . $this->table($table) . ($type !== null ? ' WHERE `type` = ?' : '') . ' ORDER BY `id` DESC LIMIT ' . $offset . ',1';

        return $this->db->fetchAssociative($sql, $type !== null ? [$type] : []) ?: null;
    }

    public function lastDate(string $table, ?string $type = null): ?\DateTimeImmutable
    {
        $row = $this->last($table, $type);

        return $row ? new \DateTimeImmutable($row['created_at']) : null;
    }

    public function countSince(string $table, \DateTimeInterface $since, ?string $type = null): int
    {
        $params = [$since->format('Y-m-d H:i:s')];
        $sql = 'SELECT count(*) FROM ' . $this->table($table) . ' WHERE `created_at` > ?';
        if ($type !== null) {
            $sql .= ' AND `type` = ?';
            $params[] = $type;
        }

        return (int)$this->db->fetchOne($sql, $params);
    }

    public function oldestDate(string $table): ?\DateTimeImmutable
    {
        $value = $this->db->fetchOne('SELECT `created_at` FROM ' . $this->table($table) . ' ORDER BY `id` ASC LIMIT 1');

        return $value ? new \DateTimeImmutable($value) : null;
    }

    public function purgeOlderThan(\DateTimeInterface $limit): void
    {
        foreach (self::PURGEABLE as $table) {
            $this->db->executeStatement('DELETE FROM ' . $this->table($table) . ' WHERE `created_at` < ?', [$limit->format('Y-m-d H:i:s')]);
        }
    }

    // --- paramètres d'eau ---

    public function addWaterParam(string $type, string $value): void
    {
        $this->db->insert(self::WATER_PARAMS, ['type' => $type, 'value' => $value]);
    }

    /**
     * N dernières mesures d'un paramètre, dans l'ordre chronologique. @return list<array<string, mixed>>
     * @return list<array<string, mixed>>
     */
    public function lastWaterParams(string $type, int $limit = 9): array
    {
        return array_reverse($this->db->fetchAllAssociative(
            'SELECT `created_at`, `value` FROM `data_parametres_eau` WHERE `type` = ? ORDER BY `id` DESC LIMIT ' . $limit,
            [$type]
        ));
    }

    // --- changements d'eau ---

    /** @return list<array<string, mixed>> */
    public function waterChanges(int $limit = 50): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `data_changement_eau` ORDER BY `id` DESC LIMIT ' . $limit);
    }

    /**
     * Dates des changements d'eau depuis $since (inclus), du plus ancien au plus récent. @return list<string>
     * @return list<string>
     */
    public function waterChangeDatesSince(string $since): array
    {
        return $this->db->fetchFirstColumn(
            'SELECT `created_at` FROM `data_changement_eau` WHERE `created_at` >= ? ORDER BY `created_at`',
            [(new \DateTimeImmutable($since))->format('Y-m-d 00:00:00')]
        );
    }

    public function totalWaterChanged(): float
    {
        return (float)$this->db->fetchOne('SELECT COALESCE(SUM(`value`), 0) FROM `data_changement_eau`');
    }

    public function deleteWaterChange(int $id): void
    {
        $this->db->delete(self::WATER_CHANGES, ['id' => $id]);
    }

    // --- nettoyages ---

    public function markCleaned(string $type): void
    {
        $this->assertCleanable($type);
        $this->db->executeStatement('INSERT INTO `' . self::CLEAN_PREFIX . $type . '` (`created_at`) VALUES (now())');
    }

    public function lastCleaned(string $type): ?\DateTimeImmutable
    {
        $this->assertCleanable($type);

        return $this->lastDate(self::CLEAN_PREFIX . $type);
    }

    private function assertCleanable(string $type): void
    {
        if (!in_array($type, self::CLEANABLE, true)) {
            throw new \InvalidArgumentException('Type de nettoyage inconnu : ' . $type);
        }
    }
}
