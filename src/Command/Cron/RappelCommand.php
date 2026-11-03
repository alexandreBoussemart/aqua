<?php

namespace App\Command\Cron;

use App\Domain\Equipment;
use App\Service\Reminders;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:rappel', "Mail quotidien (19h) : nourrissage du jour + entretiens en retard")]
final class RappelCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly Reminders $reminders)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled() || !$this->t->status->isEnabled('cron_rappel')) {
                return Command::SUCCESS;
            }
            $body = '';
            foreach (Equipment::WEEKLY_FEEDING[date('D')] as $key => $label) {
                $body .= sprintf('<p style="color: %s">%s</p>', Equipment::FEEDING_COLORS[$key], $label);
            }
            $this->t->notifier->send('Rappel - ajout à faire', $body);
            $this->reminders->notifyOverdue();
        } catch (\Throwable $e) {
            $this->t->reportError('rappel', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
