<?php

namespace App\Service;

/**
 * Prépare les données d'un graphique Chart.js (templates/_partials/chart.html.twig) :
 * points, limites (min / idéal / vigilance / max), échelle suggérée et synthèse des dépassements.
 */
final class ChartBuilder
{
    /**
     * @param list<array<string, mixed>> $rows lignes {created_at, value}
     * @param array{min?: float|null, max?: float|null, ideal?: float|null, warn?: float|null} $limits
     * @param list<string> $events dates (Y-m-d H:i:s) à marquer d'un trait vertical (changements d'eau)
     * @return array<string, mixed>
     */
    public static function build(
        string $id,
        string $label,
        string $unit,
        array $rows,
        array $limits,
        float $suggestedMin,
        float $suggestedMax,
        bool $dateOnly = false,
        array $events = [],
    ): array {
        $limits = array_filter($limits + ['min' => null, 'max' => null, 'ideal' => null, 'warn' => null], static fn($v) => $v !== null) + ['min' => null, 'max' => null, 'ideal' => null, 'warn' => null];
        $points = [];
        $above = $below = 0;
        $extreme = null;

        foreach ($rows as $row) {
            $value = (float)$row['value'];
            $date = new \DateTimeImmutable((string)$row['created_at']);
            $state = match (true) {
                $limits['max'] !== null && $value > $limits['max'] => 'above',
                $limits['min'] !== null && $value < $limits['min'] => 'below',
                default => 'ok',
            };
            if ($state === 'above') {
                $above++;
                $extreme = max($extreme ?? $value, $value);
            } elseif ($state === 'below') {
                $below++;
            }
            $points[] = [
                'x' => $date->getTimestamp() * 1000,
                'y' => $value,
                'd' => $date->format($dateOnly ? 'd/m/Y' : 'd/m/Y H:i'),
                's' => $state,
            ];
        }

        // ordre chronologique (les mesures peuvent être insérées dans le désordre)
        usort($points, static fn(array $a, array $b) => $a['x'] <=> $b['x']);
        $last = $points ? $points[array_key_last($points)] : null;

        // un seul repère par jour pour les changements d'eau, à l'heure réelle du dernier changement du jour
        // (et jamais avant la première mesure, pour rester dans l'axe)
        $eventDays = [];
        $firstX = $points ? $points[0]['x'] : null;
        foreach ($events as $event) {
            $date = new \DateTimeImmutable($event);
            $x = max($date->getTimestamp() * 1000, $firstX ?? 0);
            $day = $date->format('Y-m-d');
            $eventDays[$day] = max($eventDays[$day] ?? 0, $x);
        }
        ksort($eventDays);

        $values = array_column($points, 'y');
        $lowest = $values ? min($values) : null;
        $highest = $values ? max($values) : null;
        $summary = null;
        if ($above || $below) {
            $parts = [];
            if ($above) {
                $parts[] = sprintf('max %s %s', self::number($highest), $unit);
            }
            if ($below) {
                $parts[] = sprintf('min %s %s', self::number($lowest), $unit);
            }
            $summary = sprintf('%d valeur%s hors limites · %s', $above + $below, $above + $below > 1 ? 's' : '', implode(' · ', $parts));
        }

        return [
            'id' => $id,
            'label' => $label,
            'unit' => $unit,
            'dateOnly' => $dateOnly,
            'limits' => $limits,
            'suggestedMin' => $suggestedMin,
            'suggestedMax' => $suggestedMax,
            'events' => array_values($eventDays),
            'points' => $points,
            'outOfRange' => ['above' => $above, 'below' => $below, 'summary' => $summary],
            'last' => $last,
        ];
    }

    private static function number(?float $value): string
    {
        return $value === null ? '' : rtrim(rtrim(number_format($value, 2, ',', ' '), '0'), ',');
    }
}
