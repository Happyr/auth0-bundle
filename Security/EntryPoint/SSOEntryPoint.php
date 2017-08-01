<?php

namespace Happyr\Auth0Bundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SSOEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var CsrfTokenManager
     */
    private $csrfTokenManager;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var string
     */
    private $auth0ClientId;

    /**
     * @var string
     */
    private $auth0Domain;

    /**
     * @var string
     */
    private $callbackPath;

    /**
     * @param HttpUtils $httpUtils
     * @param $auth0ClientId
     * @param string $auth0Domain
     */
    public function __construct(CsrfTokenManager $csrfTokenManager, HttpUtils $httpUtils, $auth0ClientId, $auth0Domain, $callbackPath)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->httpUtils = $httpUtils;
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0Domain = $auth0Domain;
        $this->callbackPath = $callbackPath;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $csrfToken = $this->csrfTokenManager->getToken('auth0-sso');

        $query = [
            'client_id' => $this->auth0ClientId,
            'redirect_uri' => $this->httpUtils->generateUri($request, $this->callbackPath),
            'response_type' => 'code',
            'language' => $request->getLocale(),
            'state' => $csrfToken->getValue(),
        ];

        return new RedirectResponse(sprintf('https://%s/authorize?%s', $this->auth0Domain, http_build_query($query)));
    }
}
