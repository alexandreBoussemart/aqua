<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use App\Domain\Levels;
use App\Gpio\GpioClient;
use App\Gpio\GpioException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:ecumeur', "Écumeur : niveau du godet et pilotage du relais")]
final class EcumeurCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly GpioClient $gpio)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::ECUMEUR, E::CONTROLE_ECUMEUR, 'Écumeur')) {
                return Command::SUCCESS;
            }
            $this->t->untilEndOfMinute(function () {
                if ($this->t->timers->has(E::ECUMEUR)) {
                    $this->t->relay->off('ecumeur');
                    $this->t->activity->touch(E::CONTROLE_ECUMEUR);
                    $this->t->states->set(E::ECUMEUR, 'state_97', false, 'Écumeur - Pause timer');

                    return;
                }
                $this->readLevel();
                $this->t->activity->touch(E::CONTROLE_ECUMEUR);

                $run = $this->t->status->isEnabled('on_off_ecumeur')
                    && !$this->t->states->isIn(E::OSMOLATEUR, ['state_1'])   // niveau d'eau trop haut
                    && $this->t->states->isIn(E::ECUMEUR, ['state_2']);       // godet OK
                $run ? $this->t->relay->on('ecumeur') : $this->t->relay->off('ecumeur');
                usleep(500_000);
            }, 120, 0);
        } catch (\Throwable $e) {
            $this->t->relay->tryOff('ecumeur');
            try {
                $e instanceof GpioException
                    ? $this->t->states->set(E::ECUMEUR, 'state_3', true, 'Ecumeur - ERREUR GPIO - ' . $e->getMessage())
                    : $this->t->states->set(E::ECUMEUR, 'state_4', true, 'Cron ecumeur - ERREUR - ' . $e->getMessage());
            } catch (\Throwable) {
            }
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function readLevel(): void
    {
        $values = $this->gpio->readStable(Levels::ECUMEUR_INPUTS, 2.0);
        if ($values === null) {
            return;
        }
        if (!$this->t->status->isEnabled('on_off_ecumeur')) {
            $this->t->states->set(E::ECUMEUR, 'state_4', true, 'Ecumeur - off', true);

            return;
        }
        [$state, $error, $message] = Levels::ecumeur($values);
        $this->t->states->set(E::ECUMEUR, $state, $error, $message);
    }
}
