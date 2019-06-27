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
    private $csrfTokenManager;
    private $httpUtils;
    private $auth0ClientId;
    private $auth0Domain;
    private $scope;
    private $callbackPath;

    public function __construct(
        CsrfTokenManager $csrfTokenManager,
        HttpUtils $httpUtils,
        string $auth0ClientId,
        string $auth0Domain,
        string $scope,
        string $callbackPath
    ) {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->httpUtils = $httpUtils;
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0Domain = $auth0Domain;
        $this->scope = $scope;
        $this->callbackPath = $callbackPath;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $csrfToken = $this->csrfTokenManager->getToken('auth0-sso');

        $query = [
            'response_type' => 'code',
            'client_id' => $this->auth0ClientId,
            'redirect_uri' => $this->httpUtils->generateUri($request, $this->callbackPath),
            'state' => $csrfToken->getValue(),
            'scope' => $this->scope,
            // https://auth0.com/docs/universal-login/i18n
            'ui_locales' => $request->getLocale(),
        ];

        return new RedirectResponse(sprintf('https://%s/authorize?%s', $this->auth0Domain, http_build_query($query)));
    }
}
