<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use App\Repository\MeasureRepository;
use App\Service\SensorReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:temperature-rpi', 'Température du CPU du Raspberry : historique toutes les 15 min')]
final class TemperatureRpiCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly SensorReader $sensors, private readonly MeasureRepository $measures)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled() || !$this->t->status->isEnabled(E::TEMPERATURE_RPI) || $this->t->minute() % 15 !== 0) {
                return Command::SUCCESS;
            }
            $temperature = $this->sensors->cpuTemperature();
            if ($temperature !== null) {
                $this->measures->add(MeasureRepository::CPU_TEMPERATURE, $temperature);
            }
        } catch (\Throwable $e) {
            $this->t->reportError('temperature_rpi', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
