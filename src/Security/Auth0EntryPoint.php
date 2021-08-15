<?php

namespace Happyr\Auth0Bundle\Security;

use Auth0\SDK\Auth0;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Auth0EntryPoint implements AuthenticationEntryPointInterface
{
    private Auth0 $auth0;
    private UrlGeneratorInterface $urlGenerator;
    private string $loginCheckRoute;
    private string $targetPathParameter;

    public function __construct(Auth0 $auth0, UrlGeneratorInterface $urlGenerator, string $loginCheckRoute, string $targetPathParameter)
    {
        $this->auth0 = $auth0;
        $this->urlGenerator = $urlGenerator;
        $this->loginCheckRoute = $loginCheckRoute;
        $this->targetPathParameter = $targetPathParameter;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $redirectUrl = $this->urlGenerator->generate($this->loginCheckRoute, [
            $this->targetPathParameter => $request->getUri(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return new RedirectResponse(
            $this->auth0->login($redirectUrl, ['ui_locales' => $request->getLocale()])
        );
    }
}
