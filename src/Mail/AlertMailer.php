<?php

namespace App\Mail;

use App\Domain\Equipment;
use App\Repository\LogRepository;
use App\Repository\MeasureRepository;
use App\Repository\StateRepository;
use App\Repository\StatusRepository;
use App\Service\Reminders;

/** Mails liés aux changements d'état des équipements (cron app:cron:mail). */
final class AlertMailer
{
    public function __construct(
        private readonly StateRepository $states,
        private readonly StatusRepository $status,
        private readonly LogRepository $logs,
        private readonly MeasureRepository $measures,
        private readonly Notifier $notifier,
        private readonly Reminders $reminders,
    ) {
    }

    /** Un mail par nouvel état (erreur en rouge, retour à la normale en vert). */
    public function sendNewStates(): void
    {
        foreach ($this->states->pendingNotifications() as $state) {
            $color = $state['error'] ? "color: red; text-transform: uppercase" : 'color: green;';
            $this->notifier->trySend($state['message'], sprintf("<p style='%s'>%s</p>", $color, htmlspecialchars($state['message'])));
            $this->states->markNotified((int)$state['id']);
        }
    }

    /** Relance les erreurs toujours actives après le délai "temps_rappel_mail" (ex. "-45 minutes"). */
    public function sendReminders(string $delay): void
    {
        foreach ($this->states->errorsSince(new \DateTimeImmutable($delay)) as $state) {
            $message = str_replace('ERREUR', 'RAPPEL ERREUR', $state['message']);
            if ($state['path'] === Equipment::TEMPERATURE) {
                $parts = array_map('trim', explode('-', $message));
                array_pop($parts);
                $parts[] = $this->lastValue(MeasureRepository::WATER_TEMPERATURE, '°C');
                $message = implode(' - ', $parts);
            }
            try {
                $this->notifier->send($message, "<p style='color: red; text-transform: uppercase'>" . htmlspecialchars($message) . '</p>');
                $this->logs->log($message);
            } catch (\Throwable $e) {
                $this->logs->log($e->getMessage());
            }
            $this->states->touch((int)$state['id']);
        }
    }

    /** Rapport quotidien de 8h00. */
    public function sendDailyReport(): void
    {
        $content = "<p style='color:green;text-transform:none;'>Cron - contrôle 8h - OK</p>";
        $content .= '<p>Dernière température enregistrée : ' . $this->lastValue(MeasureRepository::WATER_TEMPERATURE, ' °C') . '</p>';
        $overdue = $this->reminders->allOverdue();
        if ($overdue) {
            $content .= '<p>RAPPEL À FAIRE :</p><ul>';
            foreach ($overdue as $message) {
                $content .= '<li>' . htmlspecialchars($message) . '</li>';
            }
            $content .= '</ul>';
        }
        $this->notifier->send('Cron - contrôle 8h - OK', $content);
    }

    /** Coupe l'envoi de mails si plus de 10 mails en 2 minutes. */
    public function preventSpam(): void
    {
        $count = $this->logs->countMailsSince(new \DateTimeImmutable('-2 minutes'));
        if ($count > 10 && $this->status->isEnabled('mail')) {
            $this->status->set('mail', false);
            $message = sprintf('Spam Mail, %d mails envoyés depuis le %s. Envoi des mails désactivé.', $count, date('d/m/Y à H:i:s'));
            $this->notifier->send('Erreur - Spam mail', '<p style="color: red;">' . $message . '</p>', true);
            $this->logs->log($message);
        }
    }

    public function lastValue(string $table, string $suffix): string
    {
        $row = $this->measures->last($table);

        return round((float)($row['value'] ?? 0), 2) . $suffix;
    }
}
