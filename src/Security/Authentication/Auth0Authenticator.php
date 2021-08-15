<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\Security\Authentication;

use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\Auth0Exception;
use Happyr\Auth0Bundle\Model\UserInfo;
use Happyr\Auth0Bundle\Security\Auth0UserProviderInterface;
use Happyr\Auth0Bundle\Security\Passport\Auth0Badge;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
    private string $loginCheckRoute;
    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator, string $loginCheckRoute)
    {
        $this->locator = $locator;
        $this->loginCheckRoute = $loginCheckRoute;
    }

    public static function getSubscribedServices()
    {
        return [
            Auth0::class,
            HttpUtils::class,
            AuthenticationSuccessHandlerInterface::class,
            AuthenticationFailureHandlerInterface::class,
            '?'.Auth0UserProviderInterface::class,
        ];
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === $this->loginCheckRoute;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $auth0 = $this->get(Auth0::class);
        try {
            /*
             * We do getUser() instead of exchange() because if the user is
             * already logged in, the getUser() will check the state first.
             *
             * We need to update the configuration with the RedirectionUrl or
             * the internal call to exchange() will fail
             */
            $auth0->configuration()->setRedirectUri($this->get(HttpUtils::class)->generateUri($request, $this->loginCheckRoute));
            $userModel = UserInfo::create($auth0->getUser());
        } catch (Auth0Exception $e) {
            throw new AuthenticationException($e->getMessage(), (int) $e->getCode(), $e);
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
