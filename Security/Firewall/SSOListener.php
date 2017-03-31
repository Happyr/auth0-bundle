<?php

namespace Happyr\Auth0Bundle\Security\Firewall;

use Happyr\Auth0Bundle\Api\Auth0;
use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\HttpUtils;

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
     * @var string
     */
    private $callbackPath;

    /**
     * @param Auth0 $auth0
     */
    public function setAuth0($auth0)
    {
        $this->auth0 = $auth0;
    }

    /**
     * @param string $callbackPath
     *
     * @return SSOListener
     */
    public function setCallbackPath($callbackPath)
    {
        $this->callbackPath = $callbackPath;

        return $this;
    }

    protected function attemptAuthentication(Request $request)
    {
        if (null === $code = $request->query->get('code')) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        $auth0Token = $this->auth0->authentication()->exchangeCodeForToken($code, [
            'redirect_uri' => $this->httpUtils->generateUri($request, $this->callbackPath),
        ]);

        $this->auth0->setAccessToken($auth0Token->getAccessToken());
        $userModel = $this->auth0->user()->userinfo();

        $token = new SSOToken($userModel);
        $token->setAccessToken($auth0Token->getAccessToken())
            ->setExpiresAt($auth0Token->getExpiresAt());

        return $this->authenticationManager->authenticate($token);
    }
}
