<?php

namespace App\Tests\Unit\Cron;

use App\Gpio\GpioClient;
use App\Gpio\GpioException;

/** Faux service GPIO en mémoire : entrées modifiables, relais mémorisés, historique des commandes. */
final class FakeGpioClient extends GpioClient
{
    /** @var array<string, int> */
    public array $inputs = ['osmolateur_trop_haut' => 0, 'osmolateur_niveau_ok' => 1, 'ecumeur_godet' => 1, 'bailling_1' => 1, 'bailling_2' => 1, 'bailling_3' => 1];
    /** @var array<string, bool> */
    public array $relays = ['ecumeur' => false, 'pompe_osmolateur' => false, 'refuge_eclairage' => false];
    /** @var list<string> */
    public array $commands = [];
    public bool $down = false;
    /** @var (\Closure(self, int): void)|null appelé à chaque lecture (simule un niveau qui évolue) */
    public ?\Closure $onRead = null;
    private int $reads = 0;

    public function __construct()
    {
        parent::__construct('fake:0');
    }

    public function read(array $inputs): array
    {
        $this->failIfDown();
        if ($this->onRead !== null) {
            ($this->onRead)($this, ++$this->reads);
        }

        return array_intersect_key($this->inputs, array_flip($inputs));
    }

    /** @return array<string, int> */
    public function readStable(array $inputs, float $delaySeconds): array
    {
        return $this->read($inputs);
    }

    public function setRelay(string $relay, bool $on): void
    {
        $this->failIfDown();
        $this->relays[$relay] = $on;
        $this->commands[] = $relay . ':' . ($on ? 'on' : 'off');
    }

    public function relays(): array
    {
        $this->failIfDown();

        return $this->relays;
    }

    private function failIfDown(): void
    {
        if ($this->down) {
            throw new GpioException('Service GPIO injoignable (test)');
        }
    }
}
