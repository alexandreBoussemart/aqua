<?php

namespace App\Command;

use App\Repository\StatusRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:status:sync', 'Recrée les fichiers var/status/* depuis la table core_config_status (installation)')]
final class StatusSyncCommand extends Command
{
    public function __construct(private readonly StatusRepository $status)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->status->syncFiles();
        $output->writeln('Fichiers de statut synchronisés.');

        return Command::SUCCESS;
    }
}
