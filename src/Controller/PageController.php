<?php

namespace App\Controller;

use App\Domain\GpioWiring;
use App\Repository\ActivityRepository;
use App\Repository\LogRepository;
use App\Repository\MeasureRepository;
use App\Repository\StateRepository;
use App\Service\Charts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Pages de consultation : logs, monitoring, schéma, câblage GPIO. */
final class PageController extends AbstractController
{
    #[Route('/logs', name: 'logs', methods: ['GET'])]
    public function logs(LogRepository $logs, MeasureRepository $measures): Response
    {
        return $this->render('logs/index.html.twig', [
            'logs' => $logs->latest(),
            'mails' => $logs->latestMails(),
            'changes' => $measures->waterChanges(),
            'total_litres' => $measures->totalWaterChanged(),
        ]);
    }

    #[Route('/monitoring', name: 'monitoring', methods: ['GET'])]
    public function monitoring(Charts $charts, StateRepository $states, ActivityRepository $activity): Response
    {
        $total = (float)@disk_total_space($this->getParameter('kernel.project_dir'));
        $free = (float)@disk_free_space($this->getParameter('kernel.project_dir'));
        $used = $total - $free;

        return $this->render('monitoring/index.html.twig', [
            'chart' => $charts->cpuTemperature(),
            'controles' => $states->controles(),
            'activity' => $activity->all(),
            'disk' => [
                'used' => round($used / 1e9, 2),
                'total' => round($total / 1e9, 2),
                'percent' => $total > 0 ? (int)ceil($used * 100 / $total) : 0,
            ],
        ]);
    }

    #[Route('/cablage-gpio', name: 'gpio', methods: ['GET'])]
    public function gpio(): Response
    {
        $connections = GpioWiring::CONNECTIONS;
        ksort($connections);

        return $this->render('gpio/index.html.twig', [
            'rows' => GpioWiring::rows(),
            'connections' => $connections,
            'type_labels' => GpioWiring::TYPE_LABELS,
        ]);
    }

    #[Route('/schema', name: 'schema', methods: ['GET'])]
    public function schema(): Response
    {
        return $this->render('schema/index.html.twig');
    }
}
