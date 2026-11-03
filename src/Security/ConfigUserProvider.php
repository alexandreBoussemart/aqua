<?php

namespace App\Security;

use App\Config\AppConfig;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Utilisateur unique déclaré dans config.json :
 *   "auth": {"username": "admin", "password_hash": "<bin/console security:hash-password>"}
 */
final class ConfigUserProvider implements UserProviderInterface
{
    public function __construct(private readonly AppConfig $config)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $auth = $this->config->auth();
        if ($auth === null || !hash_equals($auth['username'], $identifier)) {
            throw new UserNotFoundException();
        }

        return new InMemoryUser($auth['username'], $auth['password_hash'], ['ROLE_ADMIN']);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof InMemoryUser) {
            throw new UnsupportedUserException();
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === InMemoryUser::class;
    }
}
