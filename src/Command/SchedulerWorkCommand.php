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
    /** Boucle bloquée plus longtemps que ça => SIGALRM tue le processus, Docker le relance (restart: unless-stopped). */
    public const WATCHDOG_SECONDS = 120;
    /** Fichier touché à chaque tour de boucle (healthcheck Docker). */
    public const HEARTBEAT_FILE = 'var/scheduler.heartbeat';

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
        // SIGALRM : action par défaut du noyau (tuer le processus), même pendant un appel bloqué.
        // Symfony Console installe sinon son propre gestionnaire PHP, exécuté seulement quand l'appel se débloque.
        pcntl_signal(SIGALRM, SIG_DFL);
        $logger->info('Planificateur démarré');
        $heartbeat = $this->projectDir . '/' . self::HEARTBEAT_FILE;
        $lastMinute = '';
        while (!$this->stop) {
            // chien de garde : réarmé à chaque tour ; si la boucle se fige (BDD bloquée…), SIGALRM (sans
            // gestionnaire) termine le processus et Docker le redémarre
            pcntl_alarm(self::WATCHDOG_SECONDS);
            @touch($heartbeat);
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
        pcntl_alarm(0);
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
            $this->tasks->ensure((string)$command->getName());
        }
    }
}
