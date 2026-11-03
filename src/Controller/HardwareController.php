<?php

namespace App\Controller;

use App\Domain\Equipment;
use App\Gpio\GpioException;
use App\Service\Relay;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Repository\CronTaskRepository;
use App\Scheduler\Scheduler;
use Lorisleiva\CronTranslator\CronTranslator;
use Symfony\Component\Routing\Attribute\Route;

/** Test des relais et planification des crons. */
final class HardwareController extends AbstractController
{
    #[Route('/relais', name: 'relais', methods: ['GET'])]
    public function relays(Relay $relay): Response
    {
        $states = $relay->states();
        $relays = [];
        foreach (Equipment::RELAYS as $name => $label) {
            $relays[] = ['name' => $name, 'label' => $label, 'on' => $states[$name] ?? null];
        }

        return $this->render('relais/index.html.twig', ['relays' => $relays, 'gpio_available' => $states !== null]);
    }

    #[Route('/relais/{name}/{action}', name: 'relais_switch', requirements: ['action' => 'on|off'], methods: ['POST'])]
    public function switch(string $name, string $action, Relay $relay): Response
    {
        if (!isset(Equipment::RELAYS[$name])) {
            throw $this->createNotFoundException();
        }
        try {
            $relay->switch($name, $action);
            $this->addFlash('success', sprintf('%s : %s.', Equipment::RELAYS[$name], $action === 'on' ? 'allumé' : 'éteint'));
        } catch (GpioException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('relais');
    }

    #[Route('/crons', name: 'crons', methods: ['GET'])]
    public function crons(KernelInterface $kernel, CronTaskRepository $tasks): Response
    {
        $commands = $this->cronCommands($kernel);
        foreach (array_keys($commands) as $name) {
            $tasks->ensure($name);
        }
        $rows = [];
        foreach ($tasks->all() as $name => $task) {
            if (!isset($commands[$name])) {
                continue;
            }
            $valid = Scheduler::isValid($task['expression']);
            $rows[] = $task + [
                'description' => $commands[$name],
                'valid' => $valid,
                'human' => $valid ? CronTranslator::translate($task['expression'], 'fr', true) : 'Expression invalide',
                'next' => $task['enabled'] ? Scheduler::nextRun($task['expression']) : null,
            ];
        }

        return $this->render('cron/index.html.twig', ['tasks' => $rows]);
    }

    #[Route('/crons', name: 'crons_save', methods: ['POST'])]
    public function saveCrons(Request $request, KernelInterface $kernel, CronTaskRepository $tasks): Response
    {
        $expressions = $request->request->all('expression');
        $enabled = $request->request->all('enabled');
        $errors = [];
        foreach (array_keys($this->cronCommands($kernel)) as $name) {
            $expression = preg_replace('/\s+/', ' ', trim((string)($expressions[$name] ?? '')));
            if (!Scheduler::isValid($expression)) {
                $errors[] = sprintf('%s : expression « %s » invalide', $name, $expression);
                continue;
            }
            $tasks->update($name, $expression, isset($enabled[$name]));
        }
        $errors ? array_map(fn($e) => $this->addFlash('error', $e), $errors) : $this->addFlash('success', 'Planification enregistrée.');

        return $this->redirectToRoute('crons');
    }

    /** AJAX : lance une commande app:cron:* (liste blanche) et renvoie sa sortie. */
    #[Route('/crons/run', name: 'crons_run', methods: ['POST'])]
    public function run(Request $request, KernelInterface $kernel, Scheduler $scheduler): Response
    {
        $name = (string)$request->request->get('file');
        if (!array_key_exists($name, $this->cronCommands($kernel))) {
            return new Response('Commande inconnue', Response::HTTP_BAD_REQUEST);
        }
        $process = $scheduler->runNow($name);
        $output = trim($process->getOutput() . $process->getErrorOutput());

        return new Response($process->isSuccessful() ? ($output ?: 'Run ok') : "Erreur ({$process->getExitCode()}) : {$output}");
    }

    /** @return array<string, string> nom => description */
    private function cronCommands(KernelInterface $kernel): array
    {
        $application = new Application($kernel);
        $commands = [];
        foreach ($application->all('app:cron') as $command) {
            $commands[$command->getName()] = $command->getDescription();
        }
        ksort($commands);

        return $commands;
    }
}
