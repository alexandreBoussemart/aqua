<?php

namespace App\Tests\Unit;

use App\Service\Dates;
use PHPUnit\Framework\TestCase;

final class DatesTest extends TestCase
{
    public function testDaysBetweenIgnoresHours(): void
    {
        self::assertSame(1, Dates::daysBetween('2026-11-01 23:59:00', '2026-11-02 00:01:00'));
        self::assertSame(0, Dates::daysBetween('2026-11-02 00:00:00', '2026-11-02 23:00:00'));
        self::assertSame(30, Dates::daysBetween('2026-10-03', '2026-11-02'));
        self::assertSame(0, Dates::daysBetween(null));
    }

    public function testAgo(): void
    {
        self::assertSame("aujourd'hui", Dates::ago(new \DateTimeImmutable()));
        self::assertSame('hier', Dates::ago(new \DateTimeImmutable('-1 day')));
        self::assertStringStartsWith('il y a 5 jours (', Dates::ago(new \DateTimeImmutable('-5 days')));
    }
}
