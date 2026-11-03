<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Toute requête POST doit porter le jeton CSRF "app" : champ _token (formulaires)
 * ou en-tête X-CSRF-TOKEN (appels AJAX, cf. $.ajaxSetup dans base.html.twig).
 * Le formulaire de connexion a son propre jeton (géré par form_login).
 */
final class CsrfGuardSubscriber implements EventSubscriberInterface
{
    public const TOKEN_ID = 'app';

    public function __construct(private readonly CsrfTokenManagerInterface $tokens)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // après le routeur (32) et le firewall (8)
        return [KernelEvents::REQUEST => ['onRequest', 0]];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->isMethod('POST') || $request->attributes->get('_route') === 'app_login') {
            return;
        }

        $value = (string)($request->request->get('_token') ?? $request->headers->get('X-CSRF-TOKEN', ''));
        if (!$this->tokens->isTokenValid(new CsrfToken(self::TOKEN_ID, $value))) {
            throw new AccessDeniedHttpException('Jeton CSRF invalide : rechargez la page.');
        }
    }
}
