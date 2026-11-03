<?php

namespace App\Tests\Unit;

use App\Config\AppConfig;
use App\Service\SensorReader;
use PHPUnit\Framework\TestCase;

final class SensorReaderTest extends TestCase
{
    private SensorReader $reader;

    protected function setUp(): void
    {
        $this->reader = new SensorReader(new AppConfig('/dev/null'));
    }

    public function testParseValidReading(): void
    {
        $content = "7a 01 4b 46 7f ff 06 10 5c : crc=5c YES\n7a 01 4b 46 7f ff 06 10 5c t=25625\n";
        self::assertSame(25.625, $this->reader->parse($content));
    }

    public function testParseNegativeReading(): void
    {
        self::assertSame(-1.5, $this->reader->parse("xx : crc=00 YES\nxx t=-1500"));
    }

    public function testRejectsBadCrc(): void
    {
        self::assertNull($this->reader->parse("7a 01 : crc=5c NO\n7a 01 t=25625"));
    }

    public function testRejectsPowerOnResetValue(): void
    {
        self::assertNull($this->reader->parse("xx : crc=00 YES\nxx t=85000"));
    }

    public function testRejectsGarbage(): void
    {
        self::assertNull($this->reader->parse(''));
        self::assertNull($this->reader->parse('n/a'));
    }

    public function testMissingFileThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader->readRaw('/nope/w1_slave');
    }
}
