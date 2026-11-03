<?php

namespace App\Domain;

/**
 * Codes utilisés dans core_config_status / core_config_state / last_activity / timer.
 */
final class Equipment
{
    public const OSMOLATEUR = 'osmolateur';
    public const ECUMEUR = 'ecumeur';
    public const BAILLING = 'bailling';
    public const TEMPERATURE = 'temperature';
    public const TEMPERATURE_AIR = 'temperature_air';
    public const TEMPERATURE_RPI = 'temperature_rpi';

    public const CONTROLE_ECUMEUR = 'controle_ecumeur';
    public const CONTROLE_BAILLING = 'controle_bailling';
    public const CONTROLE_OSMOLATEUR = 'controle_osmolateur';
    public const CONTROLE_TEMPERATURE = 'controle_temperature';

    /** contrôle => message d'erreur si l'équipement ne s'est pas manifesté depuis 1 minute */
    public const CONTROLES = [
        self::CONTROLE_BAILLING => 'Cron - ERREUR - script bailling',
        self::CONTROLE_ECUMEUR => 'Cron - ERREUR - script écumeur',
        self::CONTROLE_OSMOLATEUR => 'Cron - ERREUR - script osmolateur',
        self::CONTROLE_TEMPERATURE => 'Cron - ERREUR - script température',
    ];

    public const DISABLE_ALL_CRON = 'disable_all_cron';

    /** équipements pouvant être mis en pause (timer) => libellé */
    public const PAUSABLE = [
        self::ECUMEUR => "L'écumeur",
        self::OSMOLATEUR => "L'osmolateur",
    ];

    /** relais pilotables (noms de config/gpio.json) => libellé */
    public const RELAYS = [
        'ecumeur' => 'Relais alimentation écumeur',
        'pompe_osmolateur' => 'Relais pompe osmolateur',
        'refuge_eclairage' => 'Relais éclairage refuge',
    ];

    /** Rappels hebdomadaires (mail quotidien + calendrier du dashboard) */
    public const WEEKLY_FEEDING = [
        'Mon' => ['surgele' => 'Nourriture congelée', 'coraux' => 'Nourriture coraux', 'bacterie' => 'Bactérie', 'algue' => 'Algue'],
        'Tue' => ['surgele' => 'Nourriture congelée'],
        'Wed' => ['surgele' => 'Nourriture congelée', 'algue' => 'Algue'],
        'Thu' => ['surgele' => 'Nourriture congelée', 'coraux' => 'Nourriture coraux'],
        'Fri' => ['surgele' => 'Nourriture congelée', 'algue' => 'Algue'],
        'Sat' => ['surgele' => 'Nourriture congelée'],
        'Sun' => ['surgele' => 'Nourriture congelée'],
    ];

    public const FEEDING_COLORS = [
        'surgele' => '#3a87ad',
        'coraux' => '#E74C3C',
        'bacterie' => '#9B59B6',
        'algue' => '#1ABB9C',
    ];
}
