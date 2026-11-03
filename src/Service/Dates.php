<?php

namespace App\Service;

final class Dates
{
    /** Nombre de jours calendaires entre deux dates (heures ignorées). */
    public static function daysBetween(\DateTimeInterface|string|null $from, \DateTimeInterface|string|null $to = 'now'): int
    {
        if ($from === null || $to === null) {
            return 0;
        }
        $a = (is_string($from) ? new \DateTimeImmutable($from) : \DateTimeImmutable::createFromInterface($from))->setTime(0, 0);
        $b = (is_string($to) ? new \DateTimeImmutable($to) : \DateTimeImmutable::createFromInterface($to))->setTime(0, 0);

        return (int)$a->diff($b)->days;
    }

    /** "aujourd'hui", "hier" ou "il y a N jours (jj/mm/aaaa)". */
    public static function ago(\DateTimeInterface|string|null $date): string
    {
        if ($date === null) {
            return '';
        }
        $days = self::daysBetween($date);
        $date = is_string($date) ? new \DateTimeImmutable($date) : $date;

        return match (true) {
            $days > 1 => sprintf('il y a %d jours (%s)', $days, $date->format('d/m/Y')),
            $days === 1 => 'hier',
            default => "aujourd'hui",
        };
    }
}
