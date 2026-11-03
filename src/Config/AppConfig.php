<?php

namespace App\Config;

/**
 * Lecture de config.json (non versionné) :
 * BDD, SMTP, destinataire des alertes, sondes 1-Wire, utilisateur de l'interface.
 */
final class AppConfig
{
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    public function __construct(private readonly string $configFile)
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->data === null) {
            if (!is_readable($this->configFile)) {
                throw new \RuntimeException(sprintf('Fichier de configuration introuvable : %s', $this->configFile));
            }
            $data = json_decode((string)file_get_contents($this->configFile), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) {
                throw new \RuntimeException(sprintf('Fichier de configuration invalide : %s', $this->configFile));
            }
            $this->data = $data;
        }

        return $this->data;
    }

    /** @return array{host: string, user: string, passwd: string, database: string} */
    public function database(): array
    {
        return $this->all()['database'][0];
    }

    /** @return array{mail: string, password: string, port: int, server: string, port_php: int, name?: string} */
    public function smtp(): array
    {
        return $this->all()['gmail'][0];
    }

    public function mailTo(): string
    {
        return (string)$this->all()['mail_to'];
    }

    /**
     * DSN Symfony Mailer : "mailer_dsn" s'il est renseigné, sinon construit depuis le bloc gmail
     * (port_php 465 => smtps/SSL, sinon smtp/STARTTLS).
     */
    public function mailerDsn(): string
    {
        if (!empty($this->all()['mailer_dsn'])) {
            return $this->all()['mailer_dsn'];
        }

        $smtp = $this->smtp();
        $port = (int)$smtp['port_php'];

        return sprintf(
            '%s://%s:%s@%s:%d',
            $port === 465 ? 'smtps' : 'smtp',
            rawurlencode($smtp['mail']),
            rawurlencode($smtp['password']),
            $smtp['server'],
            $port
        );
    }

    public function waterSensorFile(): string
    {
        return (string)($this->all()['file_temperature_eau'] ?? '');
    }

    public function airSensorFile(): string
    {
        return (string)($this->all()['file_temperature_air'] ?? '');
    }

    public function cpuTemperatureFile(): string
    {
        return (string)($this->all()['file_temperature_rpi'] ?? '/sys/class/thermal/thermal_zone0/temp');
    }

    /** @return array{username: string, password_hash: string}|null */
    public function auth(): ?array
    {
        $auth = $this->all()['auth'] ?? null;

        return (is_array($auth) && !empty($auth['username']) && !empty($auth['password_hash'])) ? $auth : null;
    }
}
