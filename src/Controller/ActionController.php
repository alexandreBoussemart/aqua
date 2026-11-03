<?php

namespace App\Controller;

use App\Domain\Equipment as E;
use App\Mail\Notifier;
use App\Repository\ConfigRepository;
use App\Repository\MeasureRepository;
use App\Repository\StatusRepository;
use App\Repository\TimerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Actions du dashboard : interrupteurs rapides, pauses, changements d'eau. */
final class ActionController extends AbstractController
{
    public function __construct(private readonly Notifier $notifier)
    {
    }

    /** AJAX : interrupteurs on/off osmolateur et écumeur. */
    #[Route('/actions-rapides', name: 'quick_actions', methods: ['POST'])]
    public function quickActions(Request $request, StatusRepository $status): Response
    {
        foreach (['on_off_osmolateur', 'on_off_ecumeur'] as $name) {
            $status->set($name, $request->request->has($name));
        }
        $this->notifier->trySend('Validation du formulaire actions rapides.', 'Validation du formulaire actions rapides.');

        return new Response('ok');
    }

    #[Route('/pause/{type}', name: 'timer_pause', requirements: ['type' => 'ecumeur|osmolateur'], methods: ['POST'])]
    public function pause(string $type, TimerRepository $timers, ConfigRepository $config): Response
    {
        $this->notify($this->pauseOne($type, $timers, $config));

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/reprise/{type}', name: 'timer_resume', requirements: ['type' => 'ecumeur|osmolateur'], methods: ['POST'])]
    public function resume(string $type, TimerRepository $timers): Response
    {
        $timers->remove($type);
        $this->notify(E::PAUSABLE[$type] . " n'est plus en pause.");

        return $this->redirectToRoute('dashboard');
    }

    /** Changement d'eau : pause de tous les équipements concernés. */
    #[Route('/pause-changement-eau', name: 'timer_water_change', methods: ['POST'])]
    public function waterChangePause(TimerRepository $timers, ConfigRepository $config): Response
    {
        $messages = [];
        foreach (array_keys(E::PAUSABLE) as $type) {
            $messages[] = $message = $this->pauseOne($type, $timers, $config);
            $this->addFlash('success', $message);
        }
        $this->notifier->trySend("Timer changement d'eau activé.", implode('<br>', $messages));

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/changement-eau', name: 'water_change_add', methods: ['POST'])]
    public function addWaterChange(Request $request, MeasureRepository $measures): Response
    {
        $value = str_replace(',', '.', (string)$request->request->get('value'));
        if (!is_numeric($value) || (float)$value <= 0) {
            $this->addFlash('error', 'Volume invalide.');

            return $this->redirectToRoute('dashboard');
        }
        $measures->add(MeasureRepository::WATER_CHANGES, (float)$value);
        $this->notify("Le changement d'eau a été sauvegardé.");

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/changement-eau/{id}/supprimer', name: 'water_change_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteWaterChange(int $id, MeasureRepository $measures): Response
    {
        $measures->deleteWaterChange($id);
        $this->notify("Le changement d'eau a été supprimé.");

        return $this->redirectToRoute('logs');
    }

    private function pauseOne(string $type, TimerRepository $timers, ConfigRepository $config): string
    {
        $until = new \DateTimeImmutable((string)$config->getRaw('timer_' . $type));
        $timers->pauseUntil($type, $until);

        return E::PAUSABLE[$type] . " a été mis en pause jusqu'à " . $until->format('H:i') . '.';
    }

    private function notify(string $message): void
    {
        $this->addFlash('success', $message);
        $this->notifier->trySend($message, $message);
    }
}
