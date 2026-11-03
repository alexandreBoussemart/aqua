<?php

namespace App\Tests\Unit;

use App\Service\WaterParams;
use PHPUnit\Framework\TestCase;

final class WaterParamsTest extends TestCase
{
    public function testStates(): void
    {
        self::assertSame('ok', WaterParams::assess('ca', 420)['state']);
        self::assertSame('ok', WaterParams::assess('ca', 450)['state'], 'la limite elle-même est dans la cible');
        self::assertSame('high', WaterParams::assess('ca', 451)['state']);
        self::assertSame('low', WaterParams::assess('potassium', 380)['state']);
        self::assertSame('warn', WaterParams::assess('nitrate', 18)['state'], 'entre vigilance (15) et max (26)');
        self::assertSame('high', WaterParams::assess('nitrate', 30)['state']);
        self::assertSame('ok', WaterParams::assess('phosphate', 0)['state']);
    }

    public function testGaugeIsClampedToScale(): void
    {
        $gauge = WaterParams::assess('ca', 420)['gauge']; // échelle 380 – 470, cible 400 – 450
        self::assertEqualsWithDelta(44.4, $gauge['value'], 0.1);
        self::assertEqualsWithDelta(22.2, $gauge['from'], 0.1);
        self::assertEqualsWithDelta(77.8, $gauge['to'], 0.1);
        self::assertSame(100.0, WaterParams::assess('ca', 9999)['gauge']['value']);
        self::assertSame(0.0, WaterParams::assess('ca', 1)['gauge']['value']);
        self::assertSame(0.0, WaterParams::assess('nitrate', 5)['gauge']['from'], 'sans min : zone depuis le bas de l\'échelle');
    }

    public function testTargetLabels(): void
    {
        self::assertSame('400 – 450', WaterParams::targetLabel('ca'));
        self::assertSame('< 15', WaterParams::targetLabel('nitrate'));
        self::assertSame('< 0,1', WaterParams::targetLabel('phosphate'));
        self::assertSame('1 024 – 1 027', WaterParams::targetLabel('densite'));
    }
}
