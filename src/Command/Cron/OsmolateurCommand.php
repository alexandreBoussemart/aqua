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

/**
 * Osmolateur : lecture des flotteurs (double lecture à 0,5 s) et pilotage de la pompe pendant une minute.
 * Sécurités : pompe coupée en premier en cas d'erreur ; durée max. de remplissage (state_8) ;
 * pendant l'appoint après niveau atteint, le flotteur « trop haut » reste surveillé ;
 * côté service GPIO, la pompe est coupée si elle n'est plus confirmée (watchdog, config/gpio.json).
 */
#[AsCommand('app:cron:osmolateur', "Osmolateur : lecture des niveaux et pilotage de la pompe (toutes les 0,5 s pendant 1 minute)")]
final class OsmolateurCommand extends Command
{
    private const PUMP = 'pompe_osmolateur';

    public function __construct(private readonly CronTools $t, private readonly GpioClient $gpio)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->t->skipEquipment(E::OSMOLATEUR, E::CONTROLE_OSMOLATEUR, 'Osmolateur')) {
                $this->t->relay->tryOff(self::PUMP);

                return Command::SUCCESS;
            }
            $maxPumpTime = (string)$this->t->config->getRaw('temps_coupure_pompe_osmolateur');
            $topUpSeconds = max(0, $this->t->config->getInt('temps_mini_pompe_osmolateur'));

            $this->t->untilEndOfMinute(function () use ($maxPumpTime, $topUpSeconds) {
                if ($this->t->timers->has(E::OSMOLATEUR)) {
                    $this->t->relay->off(self::PUMP);
                    $this->t->activity->touch(E::CONTROLE_OSMOLATEUR);
                    $this->t->states->set(E::OSMOLATEUR, 'state_97', false, 'Osmolateur - Pause timer');

                    return;
                }
                $this->readLevels($topUpSeconds);
                $this->t->activity->touch(E::CONTROLE_OSMOLATEUR);

                if ($this->t->states->isIn(E::OSMOLATEUR, ['state_3']) && !$this->pumpRunningTooLong($maxPumpTime)) {
                    $this->t->relay->on(self::PUMP);
                } else {
                    $this->t->relay->off(self::PUMP);
                }
            }, 120, 0.5);
        } catch (\Throwable $e) {
            // la pompe d'abord : l'erreur peut venir de la BDD, l'enregistrer pourrait échouer à son tour
            $this->t->relay->tryOff(self::PUMP);
            try {
                $e instanceof GpioException
                    ? $this->t->states->set(E::OSMOLATEUR, 'state_6', true, 'Osmolateur - ERREUR GPIO - ' . $e->getMessage())
                    : $this->t->states->set(E::OSMOLATEUR, 'state_7', true, 'Cron osmolateur - ERREUR - ' . $e->getMessage());
            } catch (\Throwable) {
                // BDD indisponible : la sortie en erreur suffit (journal du planificateur)
            }
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function readLevels(int $topUpSeconds): void
    {
        $values = $this->gpio->readStable(Levels::OSMOLATEUR_INPUTS, 0.5);
        if ($values === null) {
            return; // niveau en mouvement : on relira au prochain passage
        }
        if (!$this->t->status->isEnabled('on_off_osmolateur')) {
            $this->t->states->set(E::OSMOLATEUR, 'state_5', true, 'Osmolateur - off', true);

            return;
        }
        switch (Levels::osmolateur($values)) {
            case Levels::OSMO_TROP_HAUT:
                $this->t->relay->off(self::PUMP);
                $this->t->states->set(E::OSMOLATEUR, 'state_1', true, 'Osmolateur - ERREUR - niveau eau TO HIGHT');
                break;
            case Levels::OSMO_OK:
                if ($this->t->states->isIn(E::OSMOLATEUR, ['state_3']) && !$this->topUp($topUpSeconds)) {
                    break; // trop haut pendant l'appoint : état déjà mis à jour
                }
                $this->t->states->set(E::OSMOLATEUR, 'state_2', false, 'Osmolateur - niveau eau OK');
                break;
            default:
                if (!$this->t->states->isIn(E::OSMOLATEUR, ['state_8'])) {
                    $this->t->states->set(E::OSMOLATEUR, 'state_3', false, 'Osmolateur - remplissage en cours');
                }
        }
    }

    /**
     * Appoint : la pompe continue $seconds secondes après le niveau atteint, en surveillant le flotteur « trop haut ».
     * @return bool false si le niveau trop haut a été atteint (pompe coupée, état state_1)
     */
    private function topUp(int $seconds): bool
    {
        $end = microtime(true) + $seconds;
        while (microtime(true) < $end) {
            if ((int)$this->gpio->read(['osmolateur_trop_haut'])['osmolateur_trop_haut'] === 1) {
                $this->t->relay->off(self::PUMP);
                $this->t->states->set(E::OSMOLATEUR, 'state_1', true, 'Osmolateur - ERREUR - niveau eau TO HIGHT');

                return false;
            }
            $this->t->relay->on(self::PUMP); // renouvelle la commande (watchdog du service GPIO)
            usleep(250_000);
        }

        return true;
    }

    /** Sécurité : remplissage (state_3) depuis plus longtemps que $maxPumpTime (ex. "-25 seconds") => erreur state_8. */
    private function pumpRunningTooLong(string $maxPumpTime): bool
    {
        $state = $this->t->states->find(E::OSMOLATEUR);
        if ($state === null || $state['value'] !== 'state_3') {
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
