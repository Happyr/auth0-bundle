<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Provider;

use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * We may use this provider with different tokens.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SSOProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @param UserProviderInterface $userProvider
     */
    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @param SSOToken $token
     *
     * @return SSOToken
     */
    public function authenticate(TokenInterface $token)
    {
        $userModel = $token->getUserModel();
        try {
            $user = $this->userProvider->loadUserByUsername($userModel);
        } catch (UsernameNotFoundException $e) {
            $user = null;
        }

        if ($user) {
            $authenticatedToken = new SSOToken($userModel, array_merge($userModel->getRoles(), $user->getRoles()));
            $authenticatedToken->setUser($user);
            $authenticatedToken->setAccessToken($token->getAccessToken())
                ->setExpiresAt($token->getExpiresAt());

            return $authenticatedToken;
        }

        throw new AuthenticationException('The Auth0 SSO authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SSOToken;
    }
}
