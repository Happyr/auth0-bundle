<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Provider;

use Auth0\SDK\API\Authentication;
use Happyr\Auth0Bundle\Model\Authentication\UserProfile\UserInfo;
use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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
     * @var Authentication
     */
    private $authenticationApi;

    public function __construct(UserProviderInterface $userProvider, Authentication $authenticationApi)
    {
        $this->userProvider = $userProvider;
        $this->authenticationApi = $authenticationApi;
    }

    /**
     * @param SSOToken $token
     *
     * @return SSOToken
     */
    public function authenticate(TokenInterface $token)
    {
        try {
            // Fetch info from the user
            $userInfo = $this->authenticationApi->userinfo($token->getAccessToken());
            $userModel = UserInfo::create($userInfo);
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not fetch user info from Auth0', 0, $e);
        }

        try {
            $user = $this->userProvider->loadUserByUsername(null !== $userModel ? $userModel : $token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new UnsupportedUserException('Unsupported user.', 0, $e);
        }

        if (!$user) {
            throw new AuthenticationException('The Auth0 SSO authentication failed.');
        }

        $authenticatedToken = new SSOToken($this->mergeRoles($userModel->getRoles(), $user->getRoles()));
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuth0Data($token->getAuth0Data());
        $authenticatedToken->setUserModel($userModel);
        $authenticatedToken->setAuthenticated(true);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SSOToken;
    }

    /**
     * @return array
     */
    private function mergeRoles(array $a, array $b)
    {
        $roles = array_merge($a, $b);

        return array_unique($roles);
    }
}
