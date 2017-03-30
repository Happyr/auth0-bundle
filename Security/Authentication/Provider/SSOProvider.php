<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Provider;

use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * We may use this provider with different tokens
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SSOProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            $user = null;
        }

        if ($user) {
            $authenticatedToken = new SSOToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The SSO authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SSOToken;
    }
}
