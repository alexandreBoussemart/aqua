<?php

namespace App\Service;

use App\Config\AppConfig;

/** Lecture des sondes DS18B20 (1-Wire) et de la température du CPU. */
final class SensorReader
{
    public function __construct(private readonly AppConfig $config)
    {
    }

    public function waterFile(): string
    {
        return $this->config->waterSensorFile();
    }

    public function airFile(): string
    {
        return $this->config->airSensorFile();
    }

    /** @throws \RuntimeException si le fichier de la sonde n'existe pas */
    public function readRaw(string $file): string
    {
        if ($file === '' || !is_readable($file)) {
            throw new \RuntimeException("ERREUR - Le fichier : {$file} n'existe pas.");
        }

        return (string)file_get_contents($file);
    }

    /**
     * Format w1_slave : ligne 1 "... crc=xx YES", ligne 2 "... t=25625".
     * Retourne null si CRC invalide, valeur 85000 (erreur de conversion) ou format inattendu.
     */
    public function parse(string $content): ?float
    {
        $lines = preg_split('/\n/', trim($content)) ?: [];
        if (count($lines) < 2 || str_contains($lines[0], 'NO') || !preg_match('/t=(-?\d+)/', $lines[1], $m) || $m[1] === '85000') {
            return null;
        }

        return ((float)$m[1]) / 1000;
    }

    public function read(string $file): ?float
    {
        return $this->parse($this->readRaw($file));
    }

    /** Valeur affichable "25.63°C" ou message d'erreur. */
    public function display(string $file): string
    {
        try {
            $value = $this->read($file);

            return $value === null ? 'Lecture de la sonde invalide' : \App\Twig\AppExtension::number($value) . ' °C';
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
    }

    public function cpuTemperature(): ?float
    {
        $file = $this->config->cpuTemperatureFile();
        if (!is_readable($file)) {
            return null;
        }

        return round(((float)file_get_contents($file)) / 1000, 2);
    }
}
