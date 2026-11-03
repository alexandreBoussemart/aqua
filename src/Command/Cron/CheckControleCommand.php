<?php

namespace App\Command\Cron;

use App\Domain\Equipment;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:check-controle', "Vérifie que chaque cron d'équipement s'est exécuté dans la dernière minute")]
final class CheckControleCommand extends Command
{
    public function __construct(private readonly CronTools $t)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled() || !$this->t->status->isEnabled('cron_controle')) {
                return Command::SUCCESS;
            }
            $to = new \DateTimeImmutable(date('Y-m-d H:i:59'));
            $from = new \DateTimeImmutable(date('Y-m-d H:i:00', strtotime('-1 minute')));

            foreach (Equipment::CONTROLES as $controle => $errorMessage) {
                $alive = $this->t->activity->existsBetween($controle, $from, $to);
                if (!$alive) {
                    sleep(10); // laisse une chance au cron en cours
                    $alive = $this->t->activity->existsBetween($controle, $from, $to);
                }
                $alive
                    ? $this->t->states->set($controle, 'state_1', false, "Cron controle {$controle} - OK")
                    : $this->t->states->set($controle, 'state_2', true, $errorMessage);
            }
            $this->t->states->set('controle', 'state_1', false, 'Cron controle - OK', true);
        } catch (\Throwable $e) {
            $this->t->logs->log($e->getMessage());
            $this->t->states->set('controle', 'state_2', true, 'Cron controle - ERREUR - ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
