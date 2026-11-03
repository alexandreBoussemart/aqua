<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:refuge-eclairage', "Refuge : éclairage allumé pendant la plage horaire configurée")]
final class RefugeEclairageCommand extends Command
{
    public const DEFAULT_START = '22:30';
    public const DEFAULT_END = '10:00';

    public function __construct(private readonly CronTools $t)
    {
        parent::__construct();
    }

    /** Plage [début, fin[ ; si début > fin, la plage passe minuit (ex. 22:30 → 10:00). */
    public static function isOnPeriod(\DateTimeInterface $now, string $start = self::DEFAULT_START, string $end = self::DEFAULT_END): bool
    {
        $time = $now->format('H:i');
        if ($start === $end) {
            return false;
        }

        return $start < $end ? ($time >= $start && $time < $end) : ($time >= $start || $time < $end);
    }

    public static function normalizeTime(?string $value, string $default): string
    {
        return ($value !== null && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value)) ? $value : $default;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled()) {
                return Command::SUCCESS;
            }
            $start = self::normalizeTime($this->t->config->getRaw('eclairage_refuge_debut'), self::DEFAULT_START);
            $end = self::normalizeTime($this->t->config->getRaw('eclairage_refuge_fin'), self::DEFAULT_END);

            if (self::isOnPeriod(new \DateTimeImmutable(), $start, $end) && $this->t->status->isEnabled('refuge_eclairage')) {
                $this->t->relay->on('refuge_eclairage');
            } else {
                $this->t->relay->off('refuge_eclairage');
                $this->t->status->set('force_turn_on_eclairage', false);
            }
        } catch (\Throwable $e) {
            $this->t->reportError('refuge_eclairage', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
