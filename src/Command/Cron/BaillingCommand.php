<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use App\Domain\Levels;
use App\Gpio\GpioClient;
use App\Gpio\GpioException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:bailling', 'Bailling : lecture des 3 niveaux (double lecture à 20 s)')]
final class BaillingCommand extends Command
{
    public function __construct(private readonly CronTools $t, private readonly GpioClient $gpio)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::BAILLING, E::CONTROLE_BAILLING, 'Bailling')) {
                return Command::SUCCESS;
            }
            $values = $this->gpio->readStable(Levels::BAILLING_INPUTS, 20.0);
            if ($values !== null) {
                [$state, $error, $message] = Levels::bailling($values);
                $this->t->states->set(E::BAILLING, $state, $error, $message);
            }
            $this->t->activity->touch(E::CONTROLE_BAILLING);
        } catch (\Throwable $e) {
            try {
                $e instanceof GpioException
                    ? $this->t->states->set(E::BAILLING, 'state_9', true, 'Bailling - ERREUR GPIO - ' . $e->getMessage())
                    : $this->t->states->set(E::BAILLING, 'state_10', true, 'Cron bailling - ERREUR - ' . $e->getMessage());
            } catch (\Throwable) {
            }
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
