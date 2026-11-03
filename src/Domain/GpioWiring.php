<?php

namespace App\Domain;

/**
 * Câblage du connecteur GPIO 40 broches du Raspberry Pi 4 (numérotation BCM).
 * Source de la page « Câblage GPIO » ; tests/Unit/GpioWiringTest.php vérifie la cohérence avec config/gpio.json
 * (brochage utilisé par le service GPIO scripts/gpio_service.py).
 */
final class GpioWiring
{
    public const TYPE_POWER_3V3 = 'power-3v3';
    public const TYPE_POWER_5V = 'power-5v';
    public const TYPE_GROUND = 'ground';
    public const TYPE_GPIO = 'gpio';
    public const TYPE_ONEWIRE = 'onewire';
    public const TYPE_SENSOR = 'sensor';
    public const TYPE_RELAY = 'relay';

    /** broche physique => [nom, numéro BCM ou null] */
    public const HEADER = [
        1 => ['3V3', null], 2 => ['5V', null],
        3 => ['GPIO 2 (SDA)', 2], 4 => ['5V', null],
        5 => ['GPIO 3 (SCL)', 3], 6 => ['GND', null],
        7 => ['GPIO 4', 4], 8 => ['GPIO 14 (TXD)', 14],
        9 => ['GND', null], 10 => ['GPIO 15 (RXD)', 15],
        11 => ['GPIO 17', 17], 12 => ['GPIO 18', 18],
        13 => ['GPIO 27', 27], 14 => ['GND', null],
        15 => ['GPIO 22', 22], 16 => ['GPIO 23', 23],
        17 => ['3V3', null], 18 => ['GPIO 24', 24],
        19 => ['GPIO 10 (MOSI)', 10], 20 => ['GND', null],
        21 => ['GPIO 9 (MISO)', 9], 22 => ['GPIO 25', 25],
        23 => ['GPIO 11 (SCLK)', 11], 24 => ['GPIO 8 (CE0)', 8],
        25 => ['GND', null], 26 => ['GPIO 7 (CE1)', 7],
        27 => ['ID_SD', null], 28 => ['ID_SC', null],
        29 => ['GPIO 5', 5], 30 => ['GND', null],
        31 => ['GPIO 6', 6], 32 => ['GPIO 12', 12],
        33 => ['GPIO 13', 13], 34 => ['GND', null],
        35 => ['GPIO 19', 19], 36 => ['GPIO 16', 16],
        37 => ['GPIO 26', 26], 38 => ['GPIO 20', 20],
        39 => ['GND', null], 40 => ['GPIO 21', 21],
    ];

    /**
     * Branchements : broche physique => détail.
     * gpio = nom de l'entrée ou du relais dans config/gpio.json (vérifié par test).
     */
    public const CONNECTIONS = [
        1 => ['type' => self::TYPE_POWER_3V3, 'label' => 'Alimentation sondes DS18B20', 'detail' => 'VDD des sondes + résistance de tirage 4,7 kΩ vers DATA'],
        2 => ['type' => self::TYPE_POWER_5V, 'label' => 'Module relais (VCC)', 'detail' => 'Alimentation 5 V de la carte relais'],
        6 => ['type' => self::TYPE_GROUND, 'label' => 'Masse commune', 'detail' => 'GND sondes, capteurs de niveau et carte relais'],
        7 => ['type' => self::TYPE_ONEWIRE, 'bcm' => 4, 'label' => 'Sondes DS18B20 (eau + air)', 'detail' => 'Bus 1-Wire (dtoverlay=w1-gpio), les deux sondes en parallèle', 'gpio' => null],
        12 => ['type' => self::TYPE_SENSOR, 'bcm' => 18, 'label' => 'Écumeur : niveau du godet', 'detail' => 'Entrée pull-up ; 0 = godet trop plein', 'gpio' => 'ecumeur_godet'],
        15 => ['type' => self::TYPE_SENSOR, 'bcm' => 22, 'label' => 'Bailling : capteur 3', 'detail' => 'Entrée pull-up ; 0 = niveau bas', 'gpio' => 'bailling_3'],
        16 => ['type' => self::TYPE_SENSOR, 'bcm' => 23, 'label' => 'Osmolateur : niveau trop haut', 'detail' => 'Entrée pull-up ; 1 = niveau trop haut', 'gpio' => 'osmolateur_trop_haut'],
        18 => ['type' => self::TYPE_SENSOR, 'bcm' => 24, 'label' => 'Osmolateur : niveau OK', 'detail' => 'Entrée pull-up ; 0 = remplissage nécessaire', 'gpio' => 'osmolateur_niveau_ok'],
        23 => ['type' => self::TYPE_RELAY, 'bcm' => 11, 'label' => 'Relais alimentation écumeur', 'detail' => 'Sortie active à l\'état haut (HIGH = écumeur alimenté)', 'gpio' => 'ecumeur'],
        24 => ['type' => self::TYPE_SENSOR, 'bcm' => 8, 'label' => 'Bailling : capteur 1', 'detail' => 'Entrée pull-up ; 0 = niveau bas', 'gpio' => 'bailling_1'],
        31 => ['type' => self::TYPE_RELAY, 'bcm' => 6, 'label' => 'Relais éclairage refuge', 'detail' => 'Sortie active à l\'état bas (LOW = allumé)', 'gpio' => 'refuge_eclairage'],
        33 => ['type' => self::TYPE_SENSOR, 'bcm' => 13, 'label' => 'Bailling : capteur 2', 'detail' => 'Entrée pull-up ; 0 = niveau bas', 'gpio' => 'bailling_2'],
        39 => ['type' => self::TYPE_GROUND, 'label' => 'Masse carte relais', 'detail' => 'GND de la carte relais (si 2e fil de masse)'],
        40 => ['type' => self::TYPE_RELAY, 'bcm' => 21, 'label' => 'Relais pompe osmolateur', 'detail' => 'Sortie active à l\'état bas (LOW = pompe en marche)', 'gpio' => 'pompe_osmolateur'],
    ];

    public const TYPE_LABELS = [
        self::TYPE_POWER_3V3 => 'Alimentation 3,3 V',
        self::TYPE_POWER_5V => 'Alimentation 5 V',
        self::TYPE_GROUND => 'Masse (GND)',
        self::TYPE_ONEWIRE => 'Bus 1-Wire (températures)',
        self::TYPE_SENSOR => 'Capteur de niveau (entrée)',
        self::TYPE_RELAY => 'Relais (sortie)',
    ];

    public static function pinType(int $pin): string
    {
        if (isset(self::CONNECTIONS[$pin])) {
            return self::CONNECTIONS[$pin]['type'];
        }
        return match (self::HEADER[$pin][0]) {
            '3V3' => self::TYPE_POWER_3V3,
            '5V' => self::TYPE_POWER_5V,
            'GND' => self::TYPE_GROUND,
            default => self::TYPE_GPIO,
        };
    }

    /** Lignes du connecteur (broche impaire à gauche, paire à droite). @return list<array<string, mixed>> */
    public static function rows(): array
    {
        $rows = [];
        for ($odd = 1; $odd < 40; $odd += 2) {
            $rows[] = ['left' => self::pin($odd), 'right' => self::pin($odd + 1)];
        }

        return $rows;
    }

    /** @return array<string, mixed> */
    private static function pin(int $pin): array
    {
        return [
            'number' => $pin,
            'name' => self::HEADER[$pin][0],
            'type' => self::pinType($pin),
            'connection' => self::CONNECTIONS[$pin] ?? null,
        ];
    }
}
