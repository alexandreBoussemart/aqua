<?php

namespace App\Command\Cron;

use App\Service\Backup;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:dump', 'Sauvegarde de la base (zip dans var/backup) envoyée par mail')]
final class DumpCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly Backup $backup)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled()) {
                return Command::SUCCESS;
            }
            $file = $this->backup->dump();
            $this->t->notifier->send('Backup BDD', 'Backup BDD', false, $file);
            $output->writeln($file);
        } catch (\Throwable $e) {
            $this->t->reportError('dump', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
