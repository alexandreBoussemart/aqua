<?php

namespace App\Controller;

use App\Domain\Equipment as E;
use App\Mail\AlertMailer;
use App\Repository\MeasureRepository;
use App\Repository\StateRepository;
use App\Repository\StatusRepository;
use App\Repository\TimerRepository;
use App\Service\Charts;
use App\Service\Dates;
use App\Service\Reminders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    /** états bailling en erreur pour chacun des 3 capteurs */
    private const BAILLING_ERRORS = [
        1 => ['state_1', 'state_2', 'state_3', 'state_5', 'state_9', 'state_10'],
        2 => ['state_2', 'state_4', 'state_5', 'state_6', 'state_9', 'state_10'],
        3 => ['state_3', 'state_4', 'state_5', 'state_7', 'state_9', 'state_10'],
    ];

    private const ICONS = [
        E::OSMOLATEUR => 'fa-tint',
        E::ECUMEUR => 'fa-filter',
        E::TEMPERATURE => 'fa-thermometer-half',
    ];

    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(
        Reminders $reminders,
        StatusRepository $status,
        TimerRepository $timers,
        StateRepository $states,
        MeasureRepository $measures,
        AlertMailer $alerts,
        Charts $charts,
    ): Response {
        $equipments = [];
        foreach ($states->findByPaths([E::OSMOLATEUR, E::ECUMEUR, E::TEMPERATURE]) as $state) {
            $equipments[] = $state + ['icon' => self::ICONS[$state['path']] ?? 'fa-power-off', 'detail' => match (true) {
                $state['path'] === E::TEMPERATURE && !$state['error'] => $alerts->lastValue(MeasureRepository::WATER_TEMPERATURE, ' °C'),
                default => self::lastPart($state['message']),
            }];
        }

        $bailling = $states->find(E::BAILLING);
        $baillingTiles = [];
        foreach (self::BAILLING_ERRORS as $n => $errorStates) {
            $baillingTiles[] = [
                'label' => 'Bailling ' . $n,
                'error' => $bailling && in_array($bailling['value'], $errorStates, true),
                'detail' => $bailling && $bailling['value'] === 'state_99' ? self::lastPart($bailling['message']) : '',
                'date' => $bailling['created_at'] ?? null,
            ];
        }

        $timersByType = [];
        foreach (E::PAUSABLE as $type => $label) {
            $timersByType[$type] = $timers->get($type);
        }

        $lastChange = $measures->last(MeasureRepository::WATER_CHANGES);

        return $this->render('dashboard/index.html.twig', [
            'overdue' => $reminders->allOverdue(),
            'quick_statuses' => $status->findByNames(['on_off_osmolateur', 'on_off_ecumeur']),
            'timers' => $timersByType,
            'progress' => $reminders->progress(),
            'feeding' => E::WEEKLY_FEEDING,
            'today' => date('D'),
            'controles' => $states->controles(),
            'equipments' => $equipments,
            'bailling' => $baillingTiles,
            'charts' => [
                'water' => $charts->waterTemperature(),
                'air' => $charts->airTemperature(),
            ],
            'last_change' => $lastChange ? $lastChange['value'] . ' litres ' . Dates::ago($lastChange['created_at']) : '',
        ]);
    }

    private static function lastPart(?string $message): string
    {
        $parts = explode('-', (string)$message);

        return trim((string)end($parts));
    }
}
