<?php

namespace App\Tests\Unit;

use App\Domain\Equipment;
use App\Domain\GpioWiring;
use App\Domain\Levels;
use PHPUnit\Framework\TestCase;

/** Le schéma de câblage (page Câblage GPIO) doit correspondre au brochage du service GPIO (config/gpio.json). */
final class GpioWiringTest extends TestCase
{
    /** @return array{inputs: array<string, int>, relays: array<string, array{pin: int, active_low?: bool, watchdog?: int}>} */
    private static function gpioConfig(): array
    {
        return json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/config/gpio.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function testWiringMatchesGpioConfig(): void
    {
        $config = self::gpioConfig();
        $declared = [];
        foreach (GpioWiring::CONNECTIONS as $pin => $connection) {
            if (empty($connection['gpio'])) {
                continue;
            }
            $name = $connection['gpio'];
            $bcm = $connection['type'] === GpioWiring::TYPE_RELAY ? ($config['relays'][$name]['pin'] ?? null) : ($config['inputs'][$name] ?? null);
            self::assertSame($connection['bcm'], $bcm, "Broche $pin ($name) : config/gpio.json déclare GPIO " . var_export($bcm, true));
            self::assertSame($connection['bcm'], GpioWiring::HEADER[$pin][1], "Broche physique $pin ≠ GPIO {$connection['bcm']}");
            $declared[] = $name;
        }
        sort($declared);
        $expected = [...array_keys($config['inputs']), ...array_keys($config['relays'])];
        sort($expected);
        self::assertSame($expected, $declared, 'Chaque entrée / relais de config/gpio.json doit figurer sur le schéma');
    }

    public function testNamesUsedByPhpExistInGpioConfig(): void
    {
        $config = self::gpioConfig();
        self::assertSame(array_keys(Equipment::RELAYS), array_keys($config['relays']));
        foreach ([...Levels::ECUMEUR_INPUTS, ...Levels::OSMOLATEUR_INPUTS, ...Levels::BAILLING_INPUTS] as $input) {
            self::assertArrayHasKey($input, $config['inputs']);
        }
    }

    public function testNoPinUsedTwice(): void
    {
        $config = self::gpioConfig();
        $pins = [...array_values($config['inputs']), ...array_column($config['relays'], 'pin')];
        self::assertCount(count($pins), array_unique($pins));
    }

    public function testPumpHasWatchdog(): void
    {
        self::assertGreaterThan(0, self::gpioConfig()['relays']['pompe_osmolateur']['watchdog'] ?? 0, 'la pompe doit être coupée si le cron ne la confirme plus');
    }

    public function testHeaderHas40Pins(): void
    {
        self::assertCount(20, GpioWiring::rows());
    }
}
