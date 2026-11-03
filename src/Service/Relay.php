<?php

namespace App\Service;

use App\Domain\Equipment;
use App\Gpio\GpioClient;
use App\Gpio\GpioException;

/** Pilotage des relais via le service GPIO (l'état réel est celui que le service maintient). */
final class Relay
{
    public function __construct(private readonly GpioClient $gpio)
    {
    }

    public function on(string $relay): void
    {
        $this->switch($relay, 'on');
    }

    public function off(string $relay): void
    {
        $this->switch($relay, 'off');
    }

    /** Coupe sans lever d'exception (chemins d'erreur : on coupe d'abord, on signale ensuite). */
    public function tryOff(string $relay): bool
    {
        try {
            $this->off($relay);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function switch(string $relay, string $action): void
    {
        if (!isset(Equipment::RELAYS[$relay]) || !in_array($action, ['on', 'off'], true)) {
            throw new \InvalidArgumentException(sprintf('Relais inconnu : %s/%s', $relay, $action));
        }
        $this->gpio->setRelay($relay, $action === 'on');
    }

    /** @return array<string, bool>|null état de chaque relais, null si le service GPIO est injoignable */
    public function states(): ?array
    {
        try {
            return $this->gpio->relays();
        } catch (GpioException) {
            return null;
        }
    }
}
