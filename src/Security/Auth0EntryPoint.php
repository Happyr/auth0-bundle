<?php

namespace Happyr\Auth0Bundle\Security;

use Auth0\SDK\Auth0;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Auth0EntryPoint implements AuthenticationEntryPointInterface
{
    private Auth0 $auth0;
    private HttpUtils $httpUtils;
    private string $loginCheckRoute;

    public function __construct(Auth0 $auth0, HttpUtils $httpUtils, string $loginCheckRoute)
    {
        $this->auth0 = $auth0;
        $this->httpUtils = $httpUtils;
        $this->loginCheckRoute = $loginCheckRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->auth0->signup(
                $this->httpUtils->generateUri($request, $this->loginCheckRoute), [
                    'ui_locales' => $request->getLocale(),
                ]
            )
        );
    }
}
