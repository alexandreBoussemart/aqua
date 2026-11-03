<?php

namespace App\Gpio;

/**
 * Client du service GPIO permanent (scripts/gpio_service.py) : une requête JSON par ligne sur TCP.
 * Entrées et relais sont désignés par leur nom (config/gpio.json), jamais par numéro de broche.
 */
class GpioClient
{
    public function __construct(
        private readonly string $gpioService,
        private readonly float $timeout = 3.0,
    ) {
    }

    public function ping(): bool
    {
        try {
            $this->request(['op' => 'ping']);

            return true;
        } catch (GpioException) {
            return false;
        }
    }

    /** @param list<string> $inputs @return array<string, int> */
    public function read(array $inputs): array
    {
        return $this->request(['op' => 'read', 'inputs' => array_values($inputs)])['values'];
    }

    /**
     * Double lecture espacée de $delaySeconds (anti-rebond des flotteurs).
     * @param list<string> $inputs
     * @return array<string, int>|null null si les deux lectures diffèrent (niveau en mouvement)
     */
    public function readStable(array $inputs, float $delaySeconds): ?array
    {
        $first = $this->read($inputs);
        usleep((int)($delaySeconds * 1_000_000));
        $second = $this->read($inputs);

        return $first === $second ? $second : null;
    }

    public function setRelay(string $relay, bool $on): void
    {
        $this->request(['op' => 'set', 'relay' => $relay, 'on' => $on]);
    }

    /** @return array<string, bool> */
    public function relays(): array
    {
        return $this->request(['op' => 'status'])['relays'];
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    protected function request(array $payload): array
    {
        $errno = 0;
        $error = '';
        $socket = @stream_socket_client('tcp://' . $this->gpioService, $errno, $error, $this->timeout);
        if ($socket === false) {
            throw new GpioException(sprintf('Service GPIO injoignable (%s) : %s', $this->gpioService, $error ?: 'connexion refusée'));
        }
        try {
            stream_set_timeout($socket, (int)ceil($this->timeout));
            fwrite($socket, json_encode($payload, JSON_THROW_ON_ERROR) . "\n");
            $line = fgets($socket);
            if ($line === false) {
                throw new GpioException('Service GPIO : pas de réponse');
            }
            $response = json_decode($line, true);
            if (!is_array($response) || empty($response['ok'])) {
                throw new GpioException('Service GPIO : ' . (is_array($response) ? ($response['error'] ?? 'erreur') : 'réponse invalide'));
            }

            return $response;
        } finally {
            fclose($socket);
        }
    }
}
