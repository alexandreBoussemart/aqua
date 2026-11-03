<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use App\Repository\MeasureRepository;
use App\Service\SensorReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:temperature', "Température de l'eau : contrôle des seuils et historique (toutes les 5 min)")]
final class TemperatureCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly SensorReader $sensors, private readonly MeasureRepository $measures)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::TEMPERATURE, E::CONTROLE_TEMPERATURE, 'Température')) {
                return Command::SUCCESS;
            }
            $minute = $this->t->minute();
            if ($minute % 5 !== 0) {
                $this->t->activity->touch(E::CONTROLE_TEMPERATURE);

                return Command::SUCCESS;
            }

            // deux lectures à 20 s d'intervalle : on écarte les mesures aberrantes (> 10 % d'écart)
            $first = $this->readOrFail();
            if ($first === null) {
                return Command::SUCCESS;
            }
            sleep(20);
            $temperature = $this->readOrFail();
            if ($temperature === null) {
                return Command::SUCCESS;
            }
            if (!($first * 0.9 < $temperature && $temperature < $first * 1.1)) {
                $this->t->states->set(E::TEMPERATURE, 'state_3', true, "Cron temperature - ERREUR - Plus de 10% d'écart");

                return Command::SUCCESS;
            }

            if ($temperature < $this->t->config->getFloat('temperature_min')) {
                $changed = $this->t->states->set(E::TEMPERATURE, 'state_5', true, "Temperature - ERREUR Trop froid - {$temperature}°C");
            } elseif ($temperature > $this->t->config->getFloat('temperature_max')) {
                $changed = $this->t->states->set(E::TEMPERATURE, 'state_6', true, "Temperature - ERREUR Trop chaud - {$temperature}°C");
            } else {
                $changed = $this->t->states->set(E::TEMPERATURE, 'state_7', false, "Temperature - OK - {$temperature}°C");
            }
            if ($minute % 15 === 0 || $changed) {
                $this->measures->add(MeasureRepository::WATER_TEMPERATURE, $temperature);
            }
            $this->t->activity->touch(E::CONTROLE_TEMPERATURE);
        } catch (\Throwable $e) {
            $this->t->logs->log($e->getMessage());
            $this->t->states->set(E::TEMPERATURE, 'state_4', true, 'Cron temperature - ERREUR - ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function readOrFail(): ?float
    {
        try {
            $content = $this->sensors->readRaw($this->sensors->waterFile());
        } catch (\RuntimeException $e) {
            $this->t->states->set(E::TEMPERATURE, 'state_1', true, $e->getMessage());
            $this->t->logs->log($e->getMessage());

            return null;
        }
        $value = $this->sensors->parse($content);
        if ($value === null) {
            $this->t->states->set(E::TEMPERATURE, 'state_2', true, 'Cron temperature - ERREUR - Format du fichier incorrect (' . $content . ')');
        }

        return $value;
    }
}
