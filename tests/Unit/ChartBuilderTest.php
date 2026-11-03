<?php

namespace App\Tests\Unit;

use App\Service\ChartBuilder;
use PHPUnit\Framework\TestCase;

final class ChartBuilderTest extends TestCase
{
    public function testFlagsValuesOutsideLimits(): void
    {
        $rows = [
            ['created_at' => '2026-11-02 10:00:00', 'value' => '25.1'],
            ['created_at' => '2026-11-02 10:15:00', 'value' => '28.4'],
            ['created_at' => '2026-11-02 10:30:00', 'value' => '22.5'],
            ['created_at' => '2026-11-02 10:45:00', 'value' => '27'],
        ];
        $chart = ChartBuilder::build('g', 'Eau', '°C', $rows, ['min' => 23, 'max' => 27, 'ideal' => 25], 21, 29);

        self::assertSame(['ok', 'above', 'below', 'ok'], array_column($chart['points'], 's'), 'la limite elle-même est dans la zone');
        self::assertSame(1, $chart['outOfRange']['above']);
        self::assertSame(1, $chart['outOfRange']['below']);
        self::assertSame('2 valeurs hors limites · max 28,4 °C · min 22,5 °C', $chart['outOfRange']['summary']);
        self::assertSame(27.0, $chart['last']['y']);
        self::assertNull($chart['limits']['warn']);
    }

    public function testNoAlertWhenWithinLimitsOrOnlyMax(): void
    {
        $rows = [['created_at' => '2026-11-02 10:00:00', 'value' => '3'], ['created_at' => '2026-11-03 10:00:00', 'value' => '0']];
        $chart = ChartBuilder::build('g', 'Nitrate', 'mg/l', $rows, ['warn' => 15, 'max' => 26], 0, 30, true);

        self::assertNull($chart['outOfRange']['summary']);
        self::assertSame('02/11/2026', $chart['points'][0]['d']);
        self::assertNull($chart['limits']['min']);
    }

    public function testWaterChangesOnePerDayAtRealTimeAndNeverBeforeFirstPoint(): void
    {
        $rows = [['created_at' => '2026-11-01 18:00:00', 'value' => '420'], ['created_at' => '2026-11-03 05:00:00', 'value' => '430']];
        $events = [
            '2026-11-01 09:00:00', // même jour que la 1re mesure mais avant : ramené sur la 1re mesure
            '2026-11-02 10:00:00', '2026-11-02 21:00:00', // 2 changements le même jour : un seul repère
            '2026-11-03 20:00:00', // après la dernière analyse : conservé (l'axe est prolongé côté JS)
        ];
        $chart = ChartBuilder::build('g', 'Ca', 'mg/l', $rows, ['min' => 400, 'max' => 450], 380, 470, true, $events);

        $ts = static fn(string $d) => (new \DateTimeImmutable($d))->getTimestamp() * 1000;
        self::assertSame([$ts('2026-11-01 18:00:00'), $ts('2026-11-02 21:00:00'), $ts('2026-11-03 20:00:00')], $chart['events']);
    }

    public function testEmptySeries(): void
    {
        $chart = ChartBuilder::build('g', 'Air', '°C', [], ['min' => 15, 'max' => 25], 12, 28);
        self::assertSame([], $chart['points']);
        self::assertNull($chart['last']);
    }
}
