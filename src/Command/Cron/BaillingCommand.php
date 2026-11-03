<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:bailling', 'Bailling : lecture des 3 niveaux (script Python)')]
final class BaillingCommand extends Command
{
    public function __construct(private readonly CronTools $t)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::BAILLING, E::CONTROLE_BAILLING, 'Bailling')) {
                return Command::SUCCESS;
            }
            $this->t->relay->runScript('bailling.py');
        } catch (\Throwable $e) {
            $this->t->states->set(E::BAILLING, 'state_10', true, 'Cron bailling - ERREUR - ' . $e->getMessage());
            $this->t->logs->log($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
