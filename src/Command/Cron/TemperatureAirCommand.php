<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use App\Repository\MeasureRepository;
use App\Service\SensorReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:temperature-air', "Température de l'air : historique toutes les 15 min")]
final class TemperatureAirCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly SensorReader $sensors, private readonly MeasureRepository $measures)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled() || !$this->t->status->isEnabled(E::TEMPERATURE_AIR) || $this->t->minute() % 5 !== 0) {
                return Command::SUCCESS;
            }
            $file = $this->sensors->airFile();
            if (!is_readable($file)) {
                $this->t->logs->log("ERREUR - Le fichier : {$file} n'existe pas.");

                return Command::SUCCESS;
            }
            $first = $this->sensors->read($file);
            if ($first === null) {
                return Command::SUCCESS;
            }
            sleep(20);
            $temperature = $this->sensors->read($file);
            if ($temperature !== null && $first * 0.9 < $temperature && $temperature < $first * 1.1 && $this->t->minute() % 15 === 0) {
                $this->measures->add(MeasureRepository::AIR_TEMPERATURE, $temperature);
            }
        } catch (\Throwable $e) {
            $this->t->reportError('temperature_air', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
