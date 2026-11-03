<?php

namespace App\Domain;

/**
 * Interprétation des capteurs de niveau (entrées pull-up : 1 = contact ouvert, 0 = contact fermé).
 * Fonctions pures, testées dans tests/Unit/LevelsTest.php. Noms d'entrées : config/gpio.json.
 */
final class Levels
{
    public const ECUMEUR_INPUTS = ['ecumeur_godet'];
    public const OSMOLATEUR_INPUTS = ['osmolateur_trop_haut', 'osmolateur_niveau_ok'];
    public const BAILLING_INPUTS = ['bailling_1', 'bailling_2', 'bailling_3'];

    public const OSMO_TROP_HAUT = 'trop_haut';
    public const OSMO_OK = 'ok';
    public const OSMO_REMPLISSAGE = 'remplissage';

    /** Combinaison bailling 1/2/3 (1 = niveau OK) => [état, erreur, message] */
    private const BAILLING = [
        '111' => ['state_8', false, 'Bailling - OK'],
        '011' => ['state_1', true, 'Bailling - ERREUR - niveau 1 bas'],
        '001' => ['state_2', true, 'Bailling - ERREUR - niveau 1 et 2 bas'],
        '010' => ['state_3', true, 'Bailling - ERREUR - niveau 1 et 3 bas'],
        '100' => ['state_4', true, 'Bailling - ERREUR - niveau 2 et 3 bas'],
        '000' => ['state_5', true, 'Bailling - ERREUR - niveau 1, 2 et 3 bas'],
        '101' => ['state_6', true, 'Bailling - ERREUR - niveau 2 bas'],
        '110' => ['state_7', true, 'Bailling - ERREUR - niveau 3 bas'],
    ];

    /**
     * @param array<string, int> $values
     * @return array{0: string, 1: bool, 2: string}
     */
    public static function bailling(array $values): array
    {
        $key = implode('', array_map(static fn(string $input) => (string)(int)$values[$input], self::BAILLING_INPUTS));

        return self::BAILLING[$key];
    }

    /**
     * @param array<string, int> $values
     * @return array{0: string, 1: bool, 2: string}
     */
    public static function ecumeur(array $values): array
    {
        return (int)$values['ecumeur_godet'] === 0
            ? ['state_1', true, 'Ecumeur - ERREUR - niveau godet trop haut']
            : ['state_2', false, 'Ecumeur - niveau godet OK'];
    }

    /** @param array<string, int> $values @return self::OSMO_* */
    public static function osmolateur(array $values): string
    {
        return match (true) {
            (int)$values['osmolateur_trop_haut'] === 1 => self::OSMO_TROP_HAUT,
            (int)$values['osmolateur_niveau_ok'] === 1 => self::OSMO_OK,
            default => self::OSMO_REMPLISSAGE,
        };
    }
}
