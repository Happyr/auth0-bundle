# Auth0 integration with Symfony

[![Latest Version](https://img.shields.io/github/release/Happyr/auth0-bundle.svg?style=flat-square)](https://github.com/Happyr/auth0-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/auth0-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/auth0-bundle)

Integrate the new authentication system from Symfony 5.2 with Auth0.

### Installation

Install with Composer:

```bash
composer require happyr/auth0-bundle
```

Enable the bundle in bundles.php

```php
return [
    // ...
    Happyr\Auth0Bundle\HappyrAuth0Bundle::class => ['all' => true],
];
```

Add your credentials and basic settings.

```yaml
// config/packages/happyr_auth0.yaml
happyr_auth0:
    # In the sdk node, you can provide every settings provided by the auth0/auth0-PHP library
    # (https://github.com/auth0/auth0-PHP#configuration-options).
    # Only the "configuration" argument is not authorized.
    # For every parameter that reference an object, you must provide a service name.
    sdk:
        domain: '%env(AUTH0_DOMAIN)%'
        clientId: '%env(AUTH0_CLIENT_ID)%'
        clientSecret: '%env(AUTH0_SECRET)%'
        tokenCache: 'cache.app' # will reference the @cache.app service automatically
        managementTokenCache: 'cache.app'
        cookieSecret: '%kernel.secret%' # To encrypt cookie values
        scope:
          - openid # "openid" is required.
          - profile
          - email
```

You are now up and running and can use services `Auth0\SDK\Auth0`, `Auth0\SDK\API\Authentication`,
`Auth0\SDK\API\Management` and `Auth0\SDK\Configuration\SdkConfiguration`.

If you want to integrate with the authentication system there are a bit more configuration you may do.

## Authentication

Start by telling Symfony what entrypoint we use and add `auth0.authenticator` as
"custom authenticator". This will make Symfony aware of the Auth0Bundle and how to
use it.

```yaml
// config/packages/security.yml
security:
    enable_authenticator_manager: true # Use the new authentication system

    # Example user provider
    providers:
        users:
            entity:
                class: 'App\Entity\User'
                property: 'auth0Id'

    firewalls:
        default:
            pattern:  ^/.*

            # Specify the entrypoint
            entry_point: auth0.entry_point

            # Add custom authenticator
            custom_authenticators:
                - auth0.authenticator

            # Example logout path
            logout:
                path: default_logout
                target: _user_logout
                invalidate_session: true
```

Next we need to configure the behavior of the bundle.

```yaml
// config/packages/happyr_auth0.yaml
happyr_auth0:
    # ...

    firewall:
        # If a request comes into route default_login_check, we will intercept
        # it and redirect the user to auth0.
        check_route: default_login_check

        # The path or route where to redirect users on failure
        failure_path: default_logout

        # The default path or route to redirect users after login
        default_target_path: user_dashboard
```

The `failure_path` and `default_target_path` will use `Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler`
and `Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler`
to handle redirects.

You may use your own handlers by specifying the service ids:

 ```yaml
// config/packages/happyr_auth0.yaml
happyr_auth0:
    # ...

    firewall:
        # If a request comes into route default_login_check, we will intercept
        # it and redirect the user to auth0.
        check_route: default_login_check

        failure_handler: App\Security\AuthenticationHandler\MyFailureHandler
        success_handler: App\Security\AuthenticationHandler\MySuccessHandler
```

### Custom user provider

If you want to use a custom UserProvider that fetches a user with more data than
just the Auth0 id, then you may create a service that implement `Happyr\Auth0Bundle\Security\Auth0UserProviderInterface`.

Then configure the bundle to use that service:

```yaml
// config/packages/happyr_auth0.yaml
happyr_auth0:
    # ...

    firewall:
        # ..
        user_provider: App\UserProvider\Auth0UserProvider
```

## Troubleshooting

Make sure you have csrf_protection enabled.

```yaml
framework:
    csrf_protection:
        enabled: true
```

## Example configuration

Below is an example configuration. We use the `Psr6Store` to store all data in Redis
and the session key in cookies. We also define to use the `MemoryStore` when testing.

```yaml

happyr_auth0:
    sdk:
        domain: '%env(AUTH0_DOMAIN)%'
        clientId: '%env(AUTH0_CLIENT_ID)%'
        clientSecret: '%env(AUTH0_SECRET)%'
        # Use custom domain for universal login
        customDomain: '%env(AUTH0_LOGIN_DOMAIN)%'
        cookieSecret: '%kernel.secret%'
        tokenCache: 'cache.redis'
        managementTokenCache: 'cache.redis'
        transientStorage: 'auth0.storage.transient'
        sessionStorage: 'auth0.storage.session'
        scope:
            - openid # "openid" is required.
            - profile
            - email
    firewall:
        check_route: default_login_check
        failure_path: default_logout
        default_target_path: startpage

services:
    # Create a new SdkConfiguration service to be able to create
    # auth0.storage.cookie_* services without circular references

    auth0.sdk_cookie_config:
        class: Auth0\SDK\Configuration\SdkConfiguration
        arguments:
            - domain: '%env(AUTH0_DOMAIN)%'
              clientId: '%env(AUTH0_CLIENT_ID)%'
              clientSecret: '%env(AUTH0_SECRET)%'
              customDomain: '%env(AUTH0_LOGIN_DOMAIN)%'
              cookieSecret: '%kernel.secret%'

    auth0.storage.cookie_transient:
        class: Auth0\SDK\Store\CookieStore
        factory: ['@auth0.sdk_cookie_config', 'getTransientStorage']

    auth0.storage.cookie_session:
        class: Auth0\SDK\Store\CookieStore
        factory: ['@auth0.sdk_cookie_config', 'getSessionStorage']

    auth0.storage.transient:
        class: Auth0\SDK\Store\Psr6Store
        arguments: ['@auth0.storage.cookie_transient', '@cache.redis']

    auth0.storage.session:
        class: Auth0\SDK\Store\Psr6Store
        arguments: ['@auth0.storage.cookie_session', '@cache.redis']

when@test:
    services:
        test.auth0.session_storage:
            class: Auth0\SDK\Store\MemoryStore

        test.auth0.transient_storage:
            class: Auth0\SDK\Store\MemoryStore

    happyr_auth0:
        sdk:
            transientStorage: test.auth0.transient_storage
            sessionStorage: test.auth0.session_storage
```
