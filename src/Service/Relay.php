<?php

namespace App\Service;

use App\Domain\Equipment;
use Symfony\Component\Process\Process;

/**
 * Pilotage matériel : lance les scripts Python (RPi.GPIO) de scripts/.
 * Les relais on/off maintiennent aussi var/relais/<nom> (état affiché sur la page Relais).
 */
final class Relay
{
    public function __construct(private readonly string $scriptsDir, private readonly string $varDir)
    {
    }

    public function on(string $relay): string
    {
        return $this->switch($relay, 'on');
    }

    public function off(string $relay): string
    {
        return $this->switch($relay, 'off');
    }

    public function switch(string $relay, string $action): string
    {
        if (!isset(Equipment::RELAYS[$relay]) || !in_array($action, ['on', 'off'], true)) {
            throw new \InvalidArgumentException(sprintf('Relais inconnu : %s/%s', $relay, $action));
        }

        return $this->python($relay . '/' . $action . '.py');
    }

    /** Lance un script de lecture de capteurs (osmolateur.py, ecumeur.py, bailling.py). */
    public function runScript(string $script): string
    {
        if (preg_match('/^[a-z_]+\.py$/', $script) !== 1) {
            throw new \InvalidArgumentException('Script invalide : ' . $script);
        }

        return $this->python($script);
    }

    public function isOn(string $relay): bool
    {
        return isset(Equipment::RELAYS[$relay]) && is_file($this->varDir . '/relais/' . $relay);
    }

    private function python(string $relativePath): string
    {
        $process = new Process(['python3', $this->scriptsDir . '/' . $relativePath]);
        $process->setTimeout(120);
        $process->run();

        return trim($process->getOutput() . $process->getErrorOutput());
    }
}
