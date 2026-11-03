<?php

namespace App\Command\Cron;

use App\Mail\AlertMailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:mail', "Mails d'alerte : nouveaux états, rappels, rapport de 8h, anti-spam")]
final class MailCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly AlertMailer $alerts)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->allDisabled()) {
                return Command::SUCCESS;
            }
            $delay = (string)$this->t->config->getRaw('temps_rappel_mail');
            $this->t->untilEndOfMinute(function () use ($delay) {
                $this->alerts->sendNewStates();
                $this->alerts->sendReminders($delay);
            }, 60, 2);

            if (date('H:i') === '08:00') {
                $this->alerts->sendDailyReport();
            }
            $this->alerts->preventSpam();
        } catch (\Throwable $e) {
            $this->t->reportError('mail', $e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
