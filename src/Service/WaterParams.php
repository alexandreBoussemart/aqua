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

    public function __construct(private readonly MeasureRepository $measures)
    {
    }

    /**
     * Dernière valeur + évolution par rapport à la précédente.
     * @return array{value: string, unit: string, diff: float|null, days: int, ago: string}|null
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
        ];
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
