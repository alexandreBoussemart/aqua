<?php

namespace App\Twig;

use App\Command\Cron\RefugeEclairageCommand as Lighting;
use App\Repository\ConfigRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function getFunctions(): array
    {
        return [
            // plage d'éclairage du refuge (horloge jour/nuit de la barre du haut)
            new TwigFunction('lighting_period', function (): array {
                $start = Lighting::normalizeTime($this->config->getRaw('eclairage_refuge_debut'), Lighting::DEFAULT_START);
                $end = Lighting::normalizeTime($this->config->getRaw('eclairage_refuge_fin'), Lighting::DEFAULT_END);

                return ['start' => $start, 'end' => $end, 'on' => Lighting::isOnPeriod(new \DateTimeImmutable(), $start, $end)];
            }),
        ];
    }

    public function getFilters(): array
    {
        return [
            // "02/11/2026 à 21:45:12"
            new TwigFilter('date_fr', static fn($date) => $date ? self::toDate($date)->format('d/m/Y à H:i:s') : ''),
            // "02/11/2026"
            new TwigFilter('date_short', static fn($date) => $date ? self::toDate($date)->format('d/m/Y') : ''),
            // nombre à la française sans décimales inutiles : 25 → « 25 », 25.5 → « 25,5 », 1.25 → « 1,25 »
            new TwigFilter('num', static fn($value, int $decimals = 2) => self::number((float)$value, $decimals)),
        ];
    }

    public static function number(float $value, int $decimals = 2): string
    {
        $formatted = number_format($value, $decimals, ',', ' ');

        return str_contains($formatted, ',') ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    }

    private static function toDate(\DateTimeInterface|string $date): \DateTimeInterface
    {
        return is_string($date) ? new \DateTimeImmutable($date) : $date;
    }
}
