<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:timer', 'Supprime les pauses (timers) arrivées à échéance')]
final class TimerCommand extends Command
{
    public function __construct(private readonly CronTools $t)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if (!$this->t->allDisabled()) {
                $this->t->timers->removeExpired();
            }
        } catch (\Throwable $e) {
            $this->t->reportError('timer', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
