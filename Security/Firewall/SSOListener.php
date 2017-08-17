<?php

namespace Happyr\Auth0Bundle\Security\Firewall;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Exception;
use Auth0\SDK\Exception\CoreException;
use Happyr\Auth0Bundle\Model\Authorization\Token\Token;
use Happyr\Auth0Bundle\Security\Authentication\Token\SSOToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

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
     * @var CsrfTokenManager
     */
    private $csrfTokenManager;

    public function setCsrfTokenManager(CsrfTokenManager $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @param Authentication $authenticationApi
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

        if (null === $state = $request->query->get('state')) {
            throw new AuthenticationException('No state in the request.');
        }

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('auth0-sso', $state))) {
            throw new AuthenticationException('Invalid CSRF token');
        }

        try {
            $redirectUri = $this->httpUtils->generateUri($request, $this->callbackPath);
            $tokenStruct = $this->authenticationApi->codeExchange($code, $redirectUri);
        } catch (Exception\ForbiddenException $e) {
            throw new AuthenticationException($e->getMessage(), $e->getCode(), $e);
        }

        // TODO, remove this legacy code
        if (isset($tokenStruct['error'])) {
            switch ($tokenStruct['error']) {
                case 'invalid_grant':
                    throw new AuthenticationException($tokenStruct['error_description']);
                default:
                    throw new CoreException($tokenStruct['error_description']);
            }
        }

        $auth0Token = Token::create($tokenStruct);

        $token = new SSOToken();
        $token->setAuth0Data($auth0Token);

        return $this->authenticationManager->authenticate($token);
    }
}
