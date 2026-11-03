<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:ecumeur', "Écumeur : niveau du godet et pilotage du relais")]
final class EcumeurCommand extends Command
{
    public function __construct(private readonly CronTools $t)
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
                $this->t->relay->runScript('ecumeur.py');
                usleep(500_000);

                $run = $this->t->status->isEnabled('on_off_ecumeur')
                    && !$this->t->states->isIn(E::OSMOLATEUR, ['state_1'])   // niveau d'eau trop haut
                    && $this->t->states->isIn(E::ECUMEUR, ['state_2']);       // godet OK
                $run ? $this->t->relay->on('ecumeur') : $this->t->relay->off('ecumeur');
            }, 120, 0);
        } catch (\Throwable $e) {
            $this->t->states->set(E::ECUMEUR, 'state_4', true, 'Cron ecumeur - ERREUR - ' . $e->getMessage());
            $this->t->logs->log($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
