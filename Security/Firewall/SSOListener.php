<?php

namespace Happyr\Auth0Bundle\Security\Firewall;

use Happyr\Auth0Bundle\Api\Auth0;
use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SSOListener extends AbstractAuthenticationListener
{
    /**
     * @var Auth0
     */
    private $auth0;

    /**
     * @param mixed $auth0
     *
     * @return SSOListener
     */
    public function setAuth0($auth0)
    {
        $this->auth0 = $auth0;

        return $this;
    }

    protected function attemptAuthentication(Request $request)
    {
        $token = new SSOToken();

        if (null === $code = $request->query->get('code')) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        $auth0Token = $this->auth0->authentication()->exchangeCodeForToken($code);


        return $this->authenticationManager->authenticate($token);
    }
}
