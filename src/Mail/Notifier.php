<?php

namespace App\Mail;

use App\Config\AppConfig;
use App\Repository\LogRepository;
use App\Repository\StatusRepository;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Envoi des alertes par mail. Si le statut "mail" est désactivé, le mail est seulement
 * enregistré dans log_mail (sauf $force).
 */
final class Notifier
{
    private ?Mailer $mailer = null;

    public function __construct(
        private readonly AppConfig $config,
        private readonly StatusRepository $status,
        private readonly LogRepository $logs,
    ) {
    }

    public function send(string $subject, string $html, bool $force = false, ?string $attachment = null): void
    {
        if (!$force && !$this->status->isEnabled('mail')) {
            $this->logs->logMail($subject, $html);

            return;
        }

        $smtp = $this->config->smtp();
        $email = (new Email())
            ->from(new Address($smtp['mail'], $smtp['name'] ?? ''))
            ->to($this->config->mailTo())
            ->subject($subject)
            ->html($html);
        if ($attachment !== null) {
            $email->attachFromPath($attachment);
        }

        $this->mailer()->send($email);
        $this->logs->logMail($subject, $html);
    }

    /** Envoie sans jamais lever d'exception (l'erreur est loguée). */
    public function trySend(string $subject, string $html, bool $force = false): void
    {
        try {
            $this->send($subject, $html, $force);
        } catch (\Throwable $e) {
            $this->logs->log('Mail - ERREUR - ' . $e->getMessage());
        }
    }

    private function mailer(): Mailer
    {
        return $this->mailer ??= new Mailer(Transport::fromDsn($this->config->mailerDsn()));
    }
}
