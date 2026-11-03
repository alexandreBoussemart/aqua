<?php

namespace App\Tests\Unit;

use App\Command\Cron\RefugeEclairageCommand as R;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RefugeEclairageCommandTest extends TestCase
{
    public static function overnight(): iterable
    {
        // plage par défaut 22:30 → 10:00 (passe minuit), sans pause
        yield '22h29 éteint' => ['22:29', false];
        yield '22h30 allumé' => ['22:30', true];
        yield '23h35 allumé (plus de pause)' => ['23:35', true];
        yield '00h45 allumé (plus de pause)' => ['00:45', true];
        yield '09h59 allumé' => ['09:59', true];
        yield '10h00 éteint' => ['10:00', false];
        yield '15h00 éteint' => ['15:00', false];
    }

    #[DataProvider('overnight')]
    public function testOvernightPeriod(string $time, bool $expected): void
    {
        self::assertSame($expected, R::isOnPeriod(new \DateTimeImmutable('2026-11-02 ' . $time)));
    }

    public function testDaytimePeriod(): void
    {
        self::assertTrue(R::isOnPeriod(new \DateTimeImmutable('2026-11-02 12:00'), '08:00', '20:00'));
        self::assertFalse(R::isOnPeriod(new \DateTimeImmutable('2026-11-02 21:00'), '08:00', '20:00'));
        self::assertFalse(R::isOnPeriod(new \DateTimeImmutable('2026-11-02 12:00'), '10:00', '10:00'), 'début = fin : jamais allumé');
    }

    public function testNormalizeTime(): void
    {
        self::assertSame('07:15', R::normalizeTime('07:15', '22:30'));
        self::assertSame('22:30', R::normalizeTime('25:00', '22:30'));
        self::assertSame('22:30', R::normalizeTime(null, '22:30'));
        self::assertSame('22:30', R::normalizeTime('7h', '22:30'));
    }
}
