<?php

namespace Happyr\Auth0Bundle\Security;

use Auth0\SDK\Configuration\SdkConfiguration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Auth0EntryPoint implements AuthenticationEntryPointInterface
{
    private SdkConfiguration $configuration;
    private $csrfTokenManager;
    private $httpUtils;
    private $callbackRoute;

    public function __construct(
        SdkConfiguration $configuration,
        CsrfTokenManagerInterface $csrfTokenManager,
        HttpUtils $httpUtils,
        string $callbackRoute
    ) {
        $this->configuration = $configuration;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->httpUtils = $httpUtils;
        $this->callbackRoute = $callbackRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $csrfToken = $this->csrfTokenManager->getToken('auth0-sso');

        $query = [
            'response_type' => $this->configuration->getResponseType(),
            'client_id' => $this->configuration->getClientId(),
            'redirect_uri' => $this->httpUtils->generateUri($request, $this->callbackRoute),
            'state' => $csrfToken->getValue(),
            'scope' => $this->configuration->buildScopeString(),
            // https://auth0.com/docs/universal-login/i18n
            'ui_locales' => $request->getLocale(),
        ];

        return new RedirectResponse(sprintf('https://%s/authorize?%s', $this->configuration->getDomain(), http_build_query($query)));
    }
}
