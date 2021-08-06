<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\Security\Authentication;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Exception\Auth0Exception;
use Happyr\Auth0Bundle\Model\UserInfo;
use Happyr\Auth0Bundle\Security\Auth0UserProviderInterface;
use Happyr\Auth0Bundle\Security\Passport\Auth0Badge;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class Auth0Authenticator extends AbstractAuthenticator implements ServiceSubscriberInterface
{
    /**
     * @var string
     */
    private $loginCheckRoute;

    /**
     * @var ContainerInterface
     */
    private $locator;

    public function __construct(ContainerInterface $locator, string $loginCheckRoute)
    {
        $this->locator = $locator;
        $this->loginCheckRoute = $loginCheckRoute;
    }

    public static function getSubscribedServices()
    {
        return [
            Authentication::class,
            CsrfTokenManagerInterface::class,
            HttpUtils::class,
            AuthenticationSuccessHandlerInterface::class,
            AuthenticationFailureHandlerInterface::class,
            '?'.Auth0UserProviderInterface::class,
        ];
    }

    public function supports(Request $request): ?bool
    {
        if ($request->attributes->get('_route') !== $this->loginCheckRoute) {
            return false;
        }

        return $request->query->has('code') && $request->query->has('state');
    }

    public function authenticate(Request $request): PassportInterface
    {
        if (null === $code = $request->query->get('code')) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        if (null === $state = $request->query->get('state')) {
            throw new AuthenticationException('No state in the request.');
        }

        if (!$this->get(CsrfTokenManagerInterface::class)->isTokenValid(new CsrfToken('auth0-sso', (string) $state))) {
            throw new AuthenticationException('Invalid CSRF token');
        }

        try {
            $redirectUri = $this->get(HttpUtils::class)->generateUri($request, $this->loginCheckRoute);
            $response = $this->get(Authentication::class)->codeExchange((string) $code, $redirectUri);
            $tokenStruct = \json_decode($response->getBody()->__toString(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (Auth0Exception $e) {
            throw new AuthenticationException($e->getMessage(), (int) $e->getCode(), $e);
        }

        try {
            // Fetch info from the user
            $response = $this->get(Authentication::class)->userinfo($tokenStruct['access_token']);
            /** @var array $userInfo */
            $userInfo = \json_decode($response->getBody()->__toString(), true, 512, \JSON_THROW_ON_ERROR);
            $userModel = UserInfo::create($userInfo);
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not fetch user info from Auth0', 0, $e);
        }

        $userProviderCallback = null;
        if (null !== $up = $this->get(Auth0UserProviderInterface::class)) {
            $userProviderCallback = static function () use ($up, $userModel) {
                return $up->loadByUserModel($userModel);
            };
        }

        return new SelfValidatingPassport(new UserBadge($userModel->getLoginIdentifier(), $userProviderCallback), [new Auth0Badge($userModel)]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->get(AuthenticationSuccessHandlerInterface::class)->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->get(AuthenticationFailureHandlerInterface::class)->onAuthenticationFailure($request, $exception);
    }

    /**
     * @template T of object
     * @psalm-param class-string<T> $service
     *
     * @return T|null
     */
    private function get(string $service)
    {
        if ($this->locator->has($service)) {
            return $this->locator->get($service);
        }

        return null;
    }
}
