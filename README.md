# Auth0 integration with Symfony

[![Latest Version](https://img.shields.io/github/release/Happyr/auth0-bundle.svg?style=flat-square)](https://github.com/Happyr/auth0-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/auth0-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/auth0-bundle)

Integrate the new authentication system from Symfony 5.2 with Auth0.

### Installation

Install with Composer:

```bash
composer require happyr/auth0-bundle auth0/auth0-php:@alpha
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
    domain: '%env(AUTH0_DOMAIN)%'
    login_domain: '%env(AUTH0_LOGIN_DOMAIN)%'
    client_id: '%env(AUTH0_CLIENT_ID)%'
    client_secret: '%env(AUTH0_SECRET)%'
    cache: 'cache.redis'
    scope: openid profile email # "openid" is required.
```

You are now up and running and can use services `Auth0\SDK\API\Authentication` and
`Auth0\SDK\API\Management`. If you want to integrate with the authentication sysstem
there are a bit more configuration you may do.

## Authentication

Start by telling Symfony what entrypoint we use and add `auth0.authenticator` as
"custom authenticator". This will make Symfony aware of the Auth0Bundle and how to
use it .

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
