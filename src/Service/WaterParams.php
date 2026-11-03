<?php

namespace App\Service;

use App\Repository\MeasureRepository;

/** Paramètres d'eau suivis (page Analyse) : unités et réglages des graphiques. */
final class WaterParams
{
    /** type => [libellé, unité, limites, échelle suggérée [min, max]] */
    public const PARAMS = [
        'ca' => ['Ca', 'mg/l', ['min' => 400, 'ideal' => 420, 'max' => 450], [380, 470]],
        'kh' => ['Kh', 'dkh', ['min' => 6, 'ideal' => 7, 'max' => 10], [5, 11]],
        'mg' => ['Mg', 'mg/l', ['min' => 1150, 'ideal' => 1300, 'max' => 1400], [1100, 1450]],
        'densite' => ['Densité', '', ['min' => 1024, 'ideal' => 1025, 'max' => 1027], [1022, 1028]],
        'nitrate' => ['Nitrate', 'mg/l', ['warn' => 15, 'max' => 26], [0, 30]],
        'phosphate' => ['Phosphate', 'mg/l', ['warn' => 0.1, 'max' => 1], [0, 1.2]],
        'potassium' => ['Potassium', 'mg/l', ['min' => 390, 'ideal' => 400, 'max' => 410], [380, 420]],
    ];

    /** type => [symbole affiché sur la carte, nom complet] */
    public const META = [
        'ca' => ['Ca', 'Calcium'],
        'kh' => ['KH', 'Dureté carbonatée'],
        'mg' => ['Mg', 'Magnésium'],
        'densite' => ['SG', 'Densité (salinité)'],
        'nitrate' => ['NO₃', 'Nitrates'],
        'phosphate' => ['PO₄', 'Phosphates'],
        'potassium' => ['K', 'Potassium'],
    ];

    public const STATE_LABELS = ['ok' => 'Dans la cible', 'low' => 'Trop bas', 'high' => 'Trop haut', 'warn' => 'À surveiller'];

    public function __construct(private readonly MeasureRepository $measures)
    {
    }

    /**
     * Dernière valeur + évolution par rapport à la précédente, état par rapport aux limites et jauge.
     * @return array<string, mixed>|null
     */
    public function summary(string $type): ?array
    {
        $last = $this->measures->last(MeasureRepository::WATER_PARAMS, $type);
        if ($last === null) {
            return null;
        }
        $previous = $this->measures->last(MeasureRepository::WATER_PARAMS, $type, 1);

        return [
            'value' => $last['value'],
            'unit' => self::PARAMS[$type][1],
            'diff' => $previous ? round((float)$last['value'] - (float)$previous['value'], 2) : null,
            'days' => $previous ? Dates::daysBetween($previous['created_at'], $last['created_at']) : 0,
            'ago' => Dates::ago($last['created_at']),
            'date' => (new \DateTimeImmutable($last['created_at']))->format('d/m/Y'),
        ] + self::assess($type, (float)$last['value']);
    }

    /**
     * État d'une valeur (ok / low / high / warn) et positions sur la jauge (en % de l'échelle suggérée).
     * @return array{state: string, gauge: array{value: float, from: float, to: float}}
     */
    public static function assess(string $type, float $value): array
    {
        $limits = self::limits($type);
        [$scaleMin, $scaleMax] = self::PARAMS[$type][3];
        $min = $limits['min'] ?? null;
        $max = $limits['max'] ?? null;
        $warn = $limits['warn'] ?? null;
        $state = match (true) {
            $max !== null && $value > $max => 'high',
            $min !== null && $value < $min => 'low',
            $warn !== null && $value > $warn => 'warn',
            default => 'ok',
        };
        $percent = static fn(float $v) => round(max(0, min(100, ($v - $scaleMin) * 100 / ($scaleMax - $scaleMin))), 1);

        return [
            'state' => $state,
            'gauge' => [
                'value' => $percent($value),
                'from' => $percent((float)($min ?? $scaleMin)),
                'to' => $percent((float)($warn ?? $max ?? $scaleMax)),
            ],
        ];
    }

    /** @return array{min?: float|int, ideal?: float|int, warn?: float|int, max?: float|int} */
    private static function limits(string $type): array
    {
        return self::PARAMS[$type][2];
    }

    /** Plage cible lisible (« 400 – 450 », « < 15 »). */
    public static function targetLabel(string $type): string
    {
        $limits = self::limits($type);
        $fmt = static fn(float $v) => rtrim(rtrim(number_format($v, 3, ',', ' '), '0'), ',');
        if (isset($limits['min'], $limits['max'])) {
            return $fmt((float)$limits['min']) . ' – ' . $fmt((float)$limits['max']);
        }

        return '< ' . $fmt((float)($limits['warn'] ?? $limits['max'] ?? 0));
    }

    /**
     * Graphique des 9 dernières mesures + changements d'eau depuis la première mesure affichée
     * (y compris ceux faits après la dernière analyse : l'axe est alors prolongé jusqu'au changement).
     * @return array<string, mixed>
     */
    public function chart(string $type): array
    {
        [$label, $unit, $limits, [$suggestedMin, $suggestedMax]] = self::PARAMS[$type];
        $points = $this->measures->lastWaterParams($type);
        $events = $points ? $this->measures->waterChangeDatesSince($points[0]['created_at']) : [];

        return ChartBuilder::build('graph_' . $type, $label, $unit, $points, $limits, $suggestedMin, $suggestedMax, true, $events);
    }
}
