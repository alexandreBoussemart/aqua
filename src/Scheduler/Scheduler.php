<?php

namespace App\Scheduler;

use App\Repository\CronTaskRepository;
use App\Repository\LogRepository;
use Cron\CronExpression;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

/**
 * Planificateur unique : chaque minute, lance en parallèle les commandes app:cron:* dues
 * (expression cron stockée dans cron_task), sans chevauchement, et enregistre leur résultat.
 */
final class Scheduler
{
    /** @var array<string, array{process: Process, started: float}> processus en cours, par commande */
    private array $running = [];

    /** @var array<string, string> tâches dues mais encore en cours => minute d'échéance (Y-m-d H:i) */
    private array $pending = [];

    public function __construct(
        private readonly CronTaskRepository $tasks,
        private readonly LogRepository $logs,
        private readonly string $projectDir,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public static function isValid(string $expression): bool
    {
        return CronExpression::isValidExpression($expression);
    }

    public static function nextRun(string $expression): ?\DateTimeInterface
    {
        return self::isValid($expression) ? (new CronExpression($expression))->getNextRunDate() : null;
    }

    /**
     * Lance les tâches dues à la minute $now (non bloquant).
     * @return list<string> commandes lancées
     */
    public function tick(\DateTimeImmutable $now): array
    {
        $this->reap();
        $started = [];
        foreach ($this->tasks->all() as $name => $task) {
            if (!$task['enabled'] || !self::isValid($task['expression']) || !(new CronExpression($task['expression']))->isDue($now)) {
                continue;
            }
            if (isset($this->running[$name])) {
                $this->pending[$name] = $now->format('Y-m-d H:i');
                continue;
            }
            $this->start($name, $now);
            $started[] = $name;
        }

        return $started;
    }

    /** Exécute une commande et attend sa fin (bouton « Run » de la page /crons). */
    public function runNow(string $name): Process
    {
        $process = $this->process($name);
        $begin = microtime(true);
        $this->tasks->started($name, new \DateTimeImmutable());
        $process->run();
        $this->record($name, $process, microtime(true) - $begin);

        return $process;
    }

    /** Enregistre le résultat des processus terminés. @return int nombre encore en cours */
    public function reap(): int
    {
        foreach ($this->running as $name => ['process' => $process, 'started' => $begin]) {
            if (!$process->isRunning()) {
                $this->record($name, $process, microtime(true) - $begin);
                unset($this->running[$name]);
            }
        }
        // échéances différées : relance dans la même minute, sinon abandon (pas de chevauchement)
        foreach ($this->pending as $name => $minute) {
            if (isset($this->running[$name])) {
                continue;
            }
            unset($this->pending[$name]);
            if ($minute === date('Y-m-d H:i')) {
                $this->start($name, new \DateTimeImmutable());
            } else {
                $this->logger->warning("$name toujours en cours pendant toute la minute $minute : exécution sautée");
            }
        }

        return count($this->running) + count($this->pending);
    }

    private function start(string $name, \DateTimeImmutable $now): void
    {
        $process = $this->process($name);
        $process->start();
        $this->running[$name] = ['process' => $process, 'started' => microtime(true)];
        $this->tasks->started($name, $now);
        $this->logger->info("$name lancé");
    }

    private function process(string $name): Process
    {
        $process = new Process([PHP_BINDIR . '/php', $this->projectDir . '/bin/console', $name, '--no-ansi', '--no-interaction']);
        $process->setTimeout(3600);

        return $process;
    }

    private function record(string $name, Process $process, float $duration): void
    {
        $exitCode = (int)$process->getExitCode();
        $output = trim($process->getOutput() . $process->getErrorOutput());
        $this->tasks->finished($name, $exitCode, $duration, $output);
        if ($exitCode !== 0) {
            $this->logs->log(sprintf('Planificateur - ERREUR - %s (code %d) %s', $name, $exitCode, mb_substr($output, 0, 200)));
        }
        $this->logger->info(sprintf('%s terminé (code %d, %.1f s)', $name, $exitCode, $duration));
    }
}
