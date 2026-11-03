<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/** Tables log et log_mail. */
final class LogRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function log(string $message): void
    {
        $this->db->insert('log', ['message' => $message]);
    }

    public function logMail(string $subject, string $body): void
    {
        $this->db->insert('log_mail', ['sujet' => $subject, 'message' => $body]);
    }

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 50): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `log` ORDER BY `id` DESC LIMIT ' . $limit);
    }

    /** @return list<array<string, mixed>> */
    public function latestMails(int $limit = 50): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM `log_mail` ORDER BY `id` DESC LIMIT ' . $limit);
    }

    public function countMailsSince(\DateTimeInterface $since): int
    {
        return (int)$this->db->fetchOne('SELECT count(*) FROM `log_mail` WHERE `created_at` > ?', [$since->format('Y-m-d H:i:s')]);
    }
}
