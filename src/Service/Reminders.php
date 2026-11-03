<?php

namespace App\Service;

use App\Mail\Notifier;
use App\Repository\ConfigRepository;
use App\Repository\LogRepository;
use App\Repository\MeasureRepository as M;

/**
 * Rappels d'entretien : changement d'eau, nettoyages, analyses.
 * Délai (en jours) configurable dans core_config_value (check_*).
 */
final class Reminders
{
    /** clé => [table, type de paramètre, clé de config, libellé, message, sujet du mail, classe de la barre] */
    public const CHECKS = [
        'ca' => [M::WATER_PARAMS, 'ca', 'check_analyse_eau', 'Test Ca', 'Pas de mesure du Ca depuis %d jours !', 'Rappel - faire une mesure du Ca', 'progress-bar-warning'],
        'mg' => [M::WATER_PARAMS, 'mg', 'check_analyse_eau', 'Test Mg', 'Pas de mesure du Mg depuis %d jours !', 'Rappel - faire une mesure du Mg', 'progress-bar-warning'],
        'kh' => [M::WATER_PARAMS, 'kh', 'check_analyse_eau', 'Test Kh', 'Pas de mesure du Kh depuis %d jours !', 'Rappel - faire une mesure du Kh', 'progress-bar-warning'],
        'potassium' => [M::WATER_PARAMS, 'potassium', 'check_analyse_eau', 'Test Potassium', 'Pas de mesure du Potassium depuis %d jours !', 'Rappel - faire une mesure du Potassium', 'progress-bar-warning'],
        'densite' => [M::WATER_PARAMS, 'densite', 'check_analyse_eau', 'Test densité', 'Pas de mesure de la densité depuis %d jours !', 'Rappel - faire une mesure de la densité', 'progress-bar-warning'],
        'nitrate' => [M::WATER_PARAMS, 'nitrate', 'check_analyse_eau', 'Test nitrate', 'Pas de mesure de nitrate depuis %d jours !', 'Rappel - faire une mesure de Nitrate', 'progress-bar-warning'],
        'phosphate' => [M::WATER_PARAMS, 'phosphate', 'check_analyse_eau', 'Test phosphate', 'Pas de mesure de phosphate depuis %d jours !', 'Rappel - faire une mesure de Phosphate', 'progress-bar-warning'],
        'changement_eau' => [M::WATER_CHANGES, null, 'check_changement_eau', "Changement d'eau", "Pas de changement d'eau depuis %d jours !", "Rappel - faire un changement d'eau", ''],
        'refuge' => ['data_clean_refuge', null, 'check_clean_refuge', 'Nettoyage refuge', "Le refuge n'a pas été nettoyé depuis %d jours !", 'Rappel - nettoyer le refuge', 'progress-bar-success'],
        'ecumeur' => ['data_clean_ecumeur', null, 'check_clean_ecumeur', 'Nettoyage écumeur', "L'écumeur n'a pas été nettoyé depuis %d jours !", "Rappel - nettoyer l'écumeur", 'progress-bar-success'],
        'pompes' => ['data_clean_pompes', null, 'check_clean_pompes', 'Nettoyage pompes', "Les pompes n'ont pas été nettoyées depuis %d jours !", 'Rappel - nettoyer les pompes', 'progress-bar-success'],
    ];

    /** clé de config affichée sur la page Configuration => clé de CHECKS de référence */
    private const CONFIG_TO_CHECK = [
        'check_changement_eau' => 'changement_eau',
        'check_clean_refuge' => 'refuge',
        'check_clean_ecumeur' => 'ecumeur',
        'check_clean_pompes' => 'pompes',
        'check_analyse_eau' => 'ca',
    ];

    public function __construct(
        private readonly M $measures,
        private readonly ConfigRepository $config,
        private readonly LogRepository $logs,
        private readonly Notifier $notifier,
    ) {
    }

    /** Message si l'entretien est en retard, null sinon. */
    public function overdue(string $key): ?string
    {
        [$table, $type, $configKey, , $message] = self::CHECKS[$key];
        $since = new \DateTimeImmutable('-' . $this->config->getInt($configKey) . ' days');
        if ($this->measures->countSince($table, $since, $type) > 0) {
            return null;
        }

        return sprintf($message, Dates::daysBetween($this->measures->lastDate($table, $type)));
    }

    /** @return array<string, string> clé => message, uniquement les rappels en retard */
    public function allOverdue(): array
    {
        $result = [];
        foreach (array_keys(self::CHECKS) as $key) {
            if ($message = $this->overdue($key)) {
                $result[$key] = $message;
            }
        }

        return $result;
    }

    /** Envoie un mail (et un log) par rappel en retard. @return array<string, string> */
    public function notifyOverdue(): array
    {
        $overdue = $this->allOverdue();
        foreach ($overdue as $key => $message) {
            $this->notifier->send(self::CHECKS[$key][5], '<p style="color: red;">' . htmlspecialchars($message) . '</p>');
            $this->logs->log($message);
        }

        return $overdue;
    }

    /** Barres de progression du dashboard. @return list<array<string, mixed>> */
    public function progress(): array
    {
        $rows = [];
        foreach (self::CHECKS as [$table, $type, $configKey, $label, , , $barClass]) {
            $now = Dates::daysBetween($this->measures->lastDate($table, $type));
            $max = max(1, $this->config->getInt($configKey));
            $rows[] = [
                'label' => $label,
                'days' => $now,
                'max' => $max,
                'remaining' => max(0, $max - $now),
                'percent' => $now >= $max ? 100 : (int)ceil($now * 100 / $max),
                'class' => $barClass,
            ];
        }

        return $rows;
    }

    /** Encart affiché sous un délai de la page Configuration. @return array{class: string, text: string}|null */
    public function nextAlert(string $configKey): ?array
    {
        $key = self::CONFIG_TO_CHECK[$configKey] ?? null;
        if ($key === null) {
            return null;
        }
        if ($configKey === 'check_analyse_eau') {
            [$table, $type] = [M::WATER_PARAMS, null];
            $overdue = $this->overdue($key) ? sprintf("Pas d'analyse d'eau depuis %d jours !", Dates::daysBetween($this->measures->lastDate($table))) : null;
        } else {
            [$table, $type] = self::CHECKS[$key];
            $overdue = $this->overdue($key);
        }
        if ($overdue) {
            return ['class' => 'alert alert-danger', 'text' => $overdue];
        }

        $left = $this->config->getInt($configKey) - Dates::daysBetween($this->measures->lastDate($table, $type));
        $when = match (true) {
            $left <= 0 => "aujourd'hui",
            $left === 1 => 'dans 1 jour',
            default => "dans {$left} jours",
        };

        return ['class' => $left <= 2 ? 'alert alert-warning' : 'alert alert-info', 'text' => 'Prochaine alerte ' . $when];
    }
}
