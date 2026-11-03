<?php

namespace App\Controller;

use App\Mail\Notifier;
use App\Repository\ConfigRepository;
use App\Repository\MeasureRepository;
use App\Repository\StatusRepository;
use App\Service\Dates;
use App\Service\Reminders;
use App\Service\Relay;
use App\Service\SensorReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/configuration')]
final class ConfigurationController extends AbstractController
{
    public function __construct(private readonly Notifier $notifier)
    {
    }

    #[Route('', name: 'configuration', methods: ['GET'])]
    public function index(ConfigRepository $config, StatusRepository $status, Reminders $reminders, MeasureRepository $measures, SensorReader $sensors): Response
    {
        $values = $config->all();
        foreach ($values as &$value) {
            $value['alert'] = $value['show_time_left'] ? $reminders->nextAlert($value['name']) : null;
        }

        $oldest = array_filter(array_map(fn(string $t) => $measures->oldestDate($t), MeasureRepository::PURGEABLE));

        return $this->render('configuration/index.html.twig', [
            'config' => $values,
            'statuses' => $status->all(),
            'oldest_data' => $oldest ? min($oldest) : null,
            'cleanings' => $this->cleanings($measures, $config),
            'water_temperature' => $sensors->display($sensors->waterFile()),
            'air_temperature' => $sensors->display($sensors->airFile()),
        ]);
    }

    /** Cartes « nettoyage » : dernière date, jours écoulés et échéance du rappel. @return list<array<string, mixed>> */
    private function cleanings(MeasureRepository $measures, ConfigRepository $config): array
    {
        $cards = [];
        foreach (['refuge' => ['Refuge', 'fa-anchor'], 'ecumeur' => ['Écumeur', 'fa-filter'], 'pompes' => ['Pompes', 'fa-cog']] as $type => [$label, $icon]) {
            $last = $measures->lastCleaned($type);
            $days = $last ? Dates::daysBetween($last) : null;
            $max = max(1, $config->getInt('check_clean_' . $type));
            $cards[] = [
                'type' => $type,
                'label' => $label,
                'icon' => $icon,
                'last' => $last,
                'ago' => $last ? Dates::ago($last) : null,
                'days' => $days,
                'max' => $max,
                'percent' => $days === null ? 100 : min(100, (int)round($days * 100 / $max)),
                'overdue' => $days === null || $days >= $max,
            ];
        }

        return $cards;
    }

    #[Route('', name: 'configuration_save', methods: ['POST'])]
    public function save(Request $request, ConfigRepository $config): Response
    {
        $types = array_column($config->all(), 'type', 'name');
        $errors = [];
        foreach ($request->request->all() as $name => $value) {
            if (!isset($types[$name]) || !is_scalar($value)) {
                continue;
            }
            $value = trim((string)$value);
            if ($types[$name] === 'time' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value)) {
                $errors[] = "Heure invalide pour « {$name} » : {$value}";
                continue;
            }
            if ($types[$name] === 'numeric' && !is_numeric(str_replace(',', '.', $value))) {
                $errors[] = "Nombre invalide pour « {$name} » : {$value}";
                continue;
            }
            $config->set($name, $types[$name] === 'numeric' ? str_replace(',', '.', $value) : $value);
        }
        foreach ($errors as $error) {
            $this->addFlash('error', $error);
        }
        $this->notify($errors ? 'Les autres configurations ont été sauvegardées.' : 'Les configurations ont été sauvegardées.');

        return $this->redirectToRoute('configuration');
    }

    /** AJAX : interrupteurs de statut (cases cochées = actives). */
    #[Route('/statuts', name: 'configuration_status', methods: ['POST'])]
    public function saveStatus(Request $request, StatusRepository $status, Relay $relay): Response
    {
        foreach ($status->names() as $name) {
            $status->set($name, $request->request->has($name));
        }
        if ($request->request->has('force_turn_on_eclairage')) {
            $relay->on('refuge_eclairage');
        }
        $this->notifier->trySend('Validation du formulaire de changement de statut.', 'Validation du formulaire de changement de statut.');

        return new Response('ok');
    }

    #[Route('/purge', name: 'configuration_purge', methods: ['POST'])]
    public function purge(MeasureRepository $measures): Response
    {
        $measures->purgeOlderThan(new \DateTimeImmutable('-30 days'));
        $this->notify('Les données de plus de 30 jours ont été supprimées.');

        return $this->redirectToRoute('configuration');
    }

    #[Route('/nettoyage/{type}', name: 'configuration_clean', requirements: ['type' => 'refuge|ecumeur|pompes'], methods: ['POST'])]
    public function clean(string $type, MeasureRepository $measures): Response
    {
        $measures->markCleaned($type);
        $this->notify([
            'refuge' => 'Le refuge a été marqué comme nettoyé.',
            'ecumeur' => "L'écumeur a été marqué comme nettoyé.",
            'pompes' => 'Les pompes ont été marquées comme nettoyées.',
        ][$type]);

        return $this->redirectToRoute('configuration');
    }

    /** AJAX : lecture instantanée d'une sonde. */
    #[Route('/temperature/{sensor}', name: 'configuration_temperature', requirements: ['sensor' => 'eau|air'], methods: ['POST'])]
    public function temperature(string $sensor, SensorReader $sensors): Response
    {
        return new Response($sensors->display($sensor === 'eau' ? $sensors->waterFile() : $sensors->airFile()));
    }

    private function notify(string $message): void
    {
        $this->addFlash('success', $message);
        $this->notifier->trySend($message, $message);
    }
}
