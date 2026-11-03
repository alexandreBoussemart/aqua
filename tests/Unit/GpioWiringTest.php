<?php

namespace App\Tests\Unit;

use App\Domain\GpioWiring;
use PHPUnit\Framework\TestCase;

/** Le schéma de câblage doit correspondre aux broches déclarées dans les scripts Python. */
final class GpioWiringTest extends TestCase
{
    public function testPinsMatchPythonScripts(): void
    {
        $scripts = dirname(__DIR__, 2) . '/scripts/';
        foreach (GpioWiring::CONNECTIONS as $pin => $connection) {
            if (empty($connection['script'])) {
                continue;
            }
            [$file, $variable] = $connection['script'];
            $source = (string)file_get_contents($scripts . $file);
            self::assertMatchesRegularExpression('/^\s*' . $variable . '\s*=\s*(\d+)/m', $source, "$file : variable $variable introuvable");
            preg_match('/^\s*' . $variable . '\s*=\s*(\d+)/m', $source, $m);
            self::assertSame($connection['bcm'], (int)$m[1], "Broche $pin : $file déclare $variable = {$m[1]}");
            self::assertSame($connection['bcm'], GpioWiring::HEADER[$pin][1], "Broche physique $pin ≠ GPIO {$connection['bcm']}");
        }
    }

    public function testRelayOffScriptsUseSamePin(): void
    {
        $scripts = dirname(__DIR__, 2) . '/scripts/';
        foreach (glob($scripts . '*/on.py') as $on) {
            preg_match('/^relay\s*=\s*(\d+)/m', (string)file_get_contents($on), $a);
            preg_match('/^relay\s*=\s*(\d+)/m', (string)file_get_contents(dirname($on) . '/off.py'), $b);
            self::assertSame($a[1], $b[1], basename(dirname($on)) . ' : on.py et off.py pilotent des broches différentes');
        }
    }

    public function testHeaderHas40Pins(): void
    {
        self::assertCount(40, GpioWiring::HEADER);
        self::assertCount(20, GpioWiring::rows());
    }
}
