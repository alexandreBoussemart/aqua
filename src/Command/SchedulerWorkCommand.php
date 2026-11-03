<?php

namespace App\Command;

use App\Repository\CronTaskRepository;
use App\Scheduler\Scheduler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:scheduler:work', "Planificateur unique : exécute les commandes app:cron:* selon leur planification (processus permanent)")]
final class SchedulerWorkCommand extends Command
{
    private bool $stop = false;

    public function __construct(
        private readonly CronTaskRepository $tasks,
        private readonly string $projectDir,
        private readonly \App\Repository\LogRepository $logs,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('once', null, InputOption::VALUE_NONE, 'Exécute uniquement la minute courante puis attend la fin des commandes (usage depuis une crontab système)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output, [\Psr\Log\LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL]);
        $scheduler = new Scheduler($this->tasks, $this->logs, $this->projectDir, $logger);
        $this->registerNewCommands();

        if ($input->getOption('once')) {
            $scheduler->tick(new \DateTimeImmutable());
            while ($scheduler->reap() > 0) {
                usleep(500_000);
            }

            return Command::SUCCESS;
        }

        foreach ([SIGTERM, SIGINT] as $signal) {
            pcntl_signal($signal, fn() => $this->stop = true);
        }
        $logger->info('Planificateur démarré');
        $lastMinute = '';
        while (!$this->stop) {
            pcntl_signal_dispatch();
            $now = new \DateTimeImmutable();
            if ($now->format('Y-m-d H:i') !== $lastMinute) {
                $lastMinute = $now->format('Y-m-d H:i');
                $scheduler->tick($now->setTime((int)$now->format('H'), (int)$now->format('i')));
            } else {
                $scheduler->reap();
            }
            usleep(500_000);
        }
        $logger->info('Arrêt : attente de la fin des commandes en cours…');
        while ($scheduler->reap() > 0) {
            usleep(500_000);
        }

        return Command::SUCCESS;
    }

    /** Ajoute dans cron_task (planifiée chaque minute) toute nouvelle commande app:cron:*. */
    private function registerNewCommands(): void
    {
        foreach ($this->getApplication()?->all('app:cron') ?? [] as $command) {
            $this->tasks->ensure($command->getName());
        }
    }
}
