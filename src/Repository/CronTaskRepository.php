<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/** Table cron_task : planification et dernier résultat de chaque commande app:cron:*. */
final class CronTaskRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<string, array<string, mixed>> nom => tâche */
    public function all(): array
    {
        $tasks = [];
        foreach ($this->db->fetchAllAssociative('SELECT * FROM `cron_task` ORDER BY `name`') as $row) {
            $tasks[$row['name']] = $row;
        }

        return $tasks;
    }

    public function ensure(string $name, string $expression = '* * * * *'): void
    {
        $this->db->executeStatement('INSERT IGNORE INTO `cron_task` (`name`, `expression`) VALUES (?, ?)', [$name, $expression]);
    }

    public function update(string $name, string $expression, bool $enabled): void
    {
        $this->db->update('cron_task', ['expression' => $expression, 'enabled' => (int)$enabled], ['name' => $name]);
    }

    public function started(string $name, \DateTimeInterface $at): void
    {
        $this->db->update('cron_task', ['last_started_at' => $at->format('Y-m-d H:i:s')], ['name' => $name]);
    }

    public function finished(string $name, int $exitCode, float $duration, string $output): void
    {
        $this->db->update('cron_task', [
            'last_finished_at' => date('Y-m-d H:i:s'),
            'last_exit_code' => $exitCode,
            'last_duration' => round($duration, 2),
            'last_output' => mb_substr($output, -5000),
        ], ['name' => $name]);
    }
}
