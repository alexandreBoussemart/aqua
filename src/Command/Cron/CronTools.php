<?php

namespace App\Command\Cron;

use App\Domain\Equipment;
use App\Mail\Notifier;
use App\Repository\ActivityRepository;
use App\Repository\ConfigRepository;
use App\Repository\LogRepository;
use App\Repository\StateRepository;
use App\Repository\StatusRepository;
use App\Repository\TimerRepository;
use App\Service\Relay;

/** Services communs aux commandes cron. */
final class CronTools
{
    public function __construct(
        public readonly StatusRepository $status,
        public readonly StateRepository $states,
        public readonly ActivityRepository $activity,
        public readonly ConfigRepository $config,
        public readonly TimerRepository $timers,
        public readonly LogRepository $logs,
        public readonly Notifier $notifier,
        public readonly Relay $relay,
    ) {
    }

    public function allDisabled(): bool
    {
        return $this->status->isEnabled(Equipment::DISABLE_ALL_CRON);
    }

    /**
     * Garde commune des crons d'équipement : toutes crons coupées (state_98) ou équipement désactivé (state_99).
     * @return bool true si le cron doit s'arrêter
     */
    public function skipEquipment(string $equipment, string $controle, string $label): bool
    {
        if ($this->allDisabled()) {
            $this->activity->touch($controle);
            $this->states->set($equipment, 'state_98', false, 'Toutes les crons sont désactivées');

            return true;
        }
        if (!$this->status->isEnabled($equipment)) {
            $this->activity->touch($controle);
            $this->states->set($equipment, 'state_99', false, $label . ' - Désactivé');

            return true;
        }

        return false;
    }

    /**
     * Exécute $tick en boucle jusqu'à la fin de la minute courante (s'arrête à :57 pour laisser
     * le planificateur relancer la commande à la minute suivante).
     * @param callable(): void $tick
     */
    public function untilEndOfMinute(callable $tick, int $maxIterations, float $pauseSeconds): void
    {
        $end = (new \DateTimeImmutable())->format('Y-m-d H:i:57');
        for ($i = 0; $i <= $maxIterations; $i++) {
            if (date('Y-m-d H:i:s') > $end) {
                break;
            }
            $tick();
            if ($pauseSeconds > 0) {
                usleep((int)($pauseSeconds * 1_000_000));
            }
        }
    }

    public function minute(): int
    {
        return (int)date('i');
    }

    /** Log + mail d'erreur d'un cron « technique » (sans état d'équipement). */
    public function reportError(string $script, \Throwable $e): void
    {
        $this->logs->log($e->getMessage());
        $this->notifier->trySend('Erreur script ' . $script, htmlspecialchars($e->getMessage()));
    }
}
