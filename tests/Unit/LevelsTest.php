<?php

namespace App\Tests\Unit;

use App\Domain\Levels;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LevelsTest extends TestCase
{
    /** @return iterable<string, array{string, string, bool}> */
    public static function baillingCases(): iterable
    {
        yield 'tout OK' => ['111', 'state_8', false];
        yield '1 bas' => ['011', 'state_1', true];
        yield '1 et 2 bas' => ['001', 'state_2', true];
        yield '1 et 3 bas' => ['010', 'state_3', true];
        yield '2 et 3 bas' => ['100', 'state_4', true];
        yield 'tout bas' => ['000', 'state_5', true];
        yield '2 bas' => ['101', 'state_6', true];
        yield '3 bas' => ['110', 'state_7', true];
    }

    #[DataProvider('baillingCases')]
    public function testBailling(string $levels, string $state, bool $error): void
    {
        $values = array_combine(Levels::BAILLING_INPUTS, array_map('intval', str_split($levels)));
        [$s, $e] = Levels::bailling($values);
        self::assertSame([$state, $error], [$s, $e]);
    }

    public function testEcumeur(): void
    {
        self::assertSame('state_1', Levels::ecumeur(['ecumeur_godet' => 0])[0], '0 = godet trop plein');
        self::assertSame('state_2', Levels::ecumeur(['ecumeur_godet' => 1])[0]);
    }

    public function testOsmolateurTooHighWinsOverEverything(): void
    {
        self::assertSame(Levels::OSMO_TROP_HAUT, Levels::osmolateur(['osmolateur_trop_haut' => 1, 'osmolateur_niveau_ok' => 0]));
        self::assertSame(Levels::OSMO_TROP_HAUT, Levels::osmolateur(['osmolateur_trop_haut' => 1, 'osmolateur_niveau_ok' => 1]));
        self::assertSame(Levels::OSMO_OK, Levels::osmolateur(['osmolateur_trop_haut' => 0, 'osmolateur_niveau_ok' => 1]));
        self::assertSame(Levels::OSMO_REMPLISSAGE, Levels::osmolateur(['osmolateur_trop_haut' => 0, 'osmolateur_niveau_ok' => 0]));
    }
}
