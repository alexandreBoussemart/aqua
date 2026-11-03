<?php

namespace App\Service;

use App\Repository\ConfigRepository;
use App\Repository\MeasureRepository as M;

/** Graphiques des dernières 24 h (dashboard, monitoring). */
final class Charts
{
    public function __construct(private readonly M $measures, private readonly ConfigRepository $config)
    {
    }

    /** @return array<string, mixed> */
    public function waterTemperature(): array
    {
        return ChartBuilder::build('graph_temperature_eau', 'Température eau', '°C', $this->last24h(M::WATER_TEMPERATURE), [
            'min' => $this->config->getFloat('temperature_min'),
            'ideal' => 25.0,
            'max' => $this->config->getFloat('temperature_max'),
        ], 21, 29);
    }

    /** @return array<string, mixed> */
    public function airTemperature(): array
    {
        return ChartBuilder::build('graph_temperature_air', 'Température air', '°C', $this->last24h(M::AIR_TEMPERATURE), [
            'min' => 15.0,
            'max' => 25.0,
        ], 12, 28);
    }

    /** @return array<string, mixed> */
    public function cpuTemperature(): array
    {
        return ChartBuilder::build('graph_temperature_rpi', 'Température raspberry', '°C', $this->last24h(M::CPU_TEMPERATURE), [
            'warn' => 40.0,
            'max' => 55.0,
        ], 25, 65);
    }

    /** @return list<array<string, mixed>> */
    private function last24h(string $table): array
    {
        return $this->measures->between($table, new \DateTimeImmutable('-1 day'), new \DateTimeImmutable());
    }
}
