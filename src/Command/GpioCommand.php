<?php

namespace App\Command;

use App\Domain\Equipment;
use App\Gpio\GpioClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:gpio', 'Service GPIO : état des relais et des capteurs, pilotage manuel (status | on <relais> | off <relais>)')]
final class GpioCommand extends Command
{
    public function __construct(private readonly GpioClient $gpio, private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::OPTIONAL, 'status | on | off', 'status')
            ->addArgument('relay', InputArgument::OPTIONAL, 'relais : ' . implode(', ', array_keys(Equipment::RELAYS)));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = (string)$input->getArgument('action');

        if (in_array($action, ['on', 'off'], true)) {
            $relay = (string)$input->getArgument('relay');
            if (!isset(Equipment::RELAYS[$relay])) {
                $io->error('Relais inconnu. Choix : ' . implode(', ', array_keys(Equipment::RELAYS)));

                return Command::INVALID;
            }
            $this->gpio->setRelay($relay, $action === 'on');
            $io->success(sprintf('%s : %s', Equipment::RELAYS[$relay], strtoupper($action)));

            return Command::SUCCESS;
        }

        $config = json_decode((string)file_get_contents($this->projectDir . '/config/gpio.json'), true, flags: JSON_THROW_ON_ERROR);
        $io->section('Relais');
        $rows = [];
        foreach ($this->gpio->relays() as $name => $on) {
            $rows[] = [$name, 'GPIO ' . $config['relays'][$name]['pin'], $on ? '<info>ON</info>' : 'OFF'];
        }
        $io->table(['Relais', 'Broche', 'État'], $rows);

        $io->section('Capteurs (1 = contact ouvert)');
        $rows = [];
        foreach ($this->gpio->read(array_keys($config['inputs'])) as $name => $value) {
            $rows[] = [$name, 'GPIO ' . $config['inputs'][$name], $value];
        }
        $io->table(['Entrée', 'Broche', 'Valeur'], $rows);

        return Command::SUCCESS;
    }
}
