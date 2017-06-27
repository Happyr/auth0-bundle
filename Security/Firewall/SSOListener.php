<?php

namespace Happyr\Auth0Bundle\Security\Firewall;

use Auth0\SDK\API\Authentication;
use Happyr\Auth0Bundle\Api\Model\Authorization\Token\Token;
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
     * @var Authentication
     */
    private $authenticationApi;

    /**
     * @var string
     */
    private $callbackPath;

    /**
     * @param Auth0 $auth0
     */
    public function setAuthenticationApi($authenticationApi)
    {
        $this->authenticationApi = $authenticationApi;
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

        $auth0Token = Token::create($this->authenticationApi
            ->code_exchange($code, $this->httpUtils->generateUri($request, $this->callbackPath)));


        $token = new SSOToken();
        $token->setAccessToken($auth0Token->getAccessToken())
            ->setExpiresAt($auth0Token->getExpiresAt());

        return $this->authenticationManager->authenticate($token);
    }
}
