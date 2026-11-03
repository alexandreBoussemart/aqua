<?php

namespace App\Command\Cron;

use App\Domain\Equipment as E;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cron:osmolateur', "Osmolateur : lecture des niveaux et pilotage de la pompe (toutes les 0,5 s pendant 1 minute)")]
final class OsmolateurCommand extends Command
{
    public function __construct(private readonly CronTools $t)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::OSMOLATEUR, E::CONTROLE_OSMOLATEUR, 'Osmolateur')) {
                return Command::SUCCESS;
            }
            $maxPumpTime = (string)$this->t->config->getRaw('temps_coupure_pompe_osmolateur');

            $this->t->untilEndOfMinute(function () use ($maxPumpTime) {
                if ($this->t->timers->has(E::OSMOLATEUR)) {
                    $this->t->activity->touch(E::CONTROLE_OSMOLATEUR);
                    $this->t->states->set(E::OSMOLATEUR, 'state_97', false, 'Osmolateur - Pause timer');
                    $this->t->relay->off('pompe_osmolateur');

                    return;
                }
                $this->t->relay->runScript('osmolateur.py');
                if ($this->t->states->isIn(E::OSMOLATEUR, ['state_3']) && !$this->pumpRunningTooLong($maxPumpTime)) {
                    $this->t->relay->on('pompe_osmolateur');
                } else {
                    $this->t->relay->off('pompe_osmolateur');
                }
            }, 120, 0.5);
        } catch (\Throwable $e) {
            $this->t->states->set(E::OSMOLATEUR, 'state_7', true, 'Cron osmolateur - ERREUR - ' . $e->getMessage());
            $this->t->logs->log($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /** Sécurité : remplissage (state_3) depuis plus longtemps que $maxPumpTime (ex. "-25 seconds") => erreur state_8. */
    private function pumpRunningTooLong(string $maxPumpTime): bool
    {
        $state = $this->t->states->find(E::OSMOLATEUR);
        if ($state === null || !in_array($state['value'], ['state_3', 'state_8'], true)) {
            return true;
        }
        if ($state['value'] === 'state_8') {
            return true;
        }
        if (new \DateTimeImmutable($state['created_at']) > new \DateTimeImmutable($maxPumpTime)) {
            return false;
        }
        $seconds = preg_replace('~\D~', '', $maxPumpTime);
        $this->t->states->set(E::OSMOLATEUR, 'state_8', true, "Osmolateur - ERREUR - pompe allumée plus de {$seconds} secondes");

        return true;
    }
}
