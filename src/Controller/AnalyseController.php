<?php

namespace App\Controller;

use App\Mail\Notifier;
use App\Repository\MeasureRepository;
use App\Service\WaterParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnalyseController extends AbstractController
{
    #[Route('/analyse', name: 'analyse', methods: ['GET'])]
    public function index(WaterParams $params): Response
    {
        $types = array_keys(WaterParams::PARAMS);

        return $this->render('analyse/index.html.twig', [
            'params' => WaterParams::PARAMS,
            'meta' => WaterParams::META,
            'targets' => array_combine($types, array_map(WaterParams::targetLabel(...), $types)),
            'state_labels' => WaterParams::STATE_LABELS,
            'summaries' => array_combine($types, array_map($params->summary(...), $types)),
            'charts' => array_combine($types, array_map($params->chart(...), $types)),
        ]);
    }

    #[Route('/analyse', name: 'analyse_save', methods: ['POST'])]
    public function save(Request $request, MeasureRepository $measures, Notifier $notifier): Response
    {
        $saved = 0;
        foreach (array_keys(WaterParams::PARAMS) as $type) {
            $value = str_replace(',', '.', trim((string)$request->request->get('value_' . $type)));
            if ($value !== '' && is_numeric($value)) {
                $measures->addWaterParam($type, $value);
                $saved++;
            }
        }
        $message = $saved ? "Les paramètres d'eau ont été sauvegardés." : 'Aucune valeur saisie.';
        $this->addFlash($saved ? 'success' : 'warning', $message);
        if ($saved) {
            $notifier->trySend($message, $message);
        }

        return $this->redirectToRoute('analyse');
    }
}
