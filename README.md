# Auth0 integration with Symfony

[![Latest Version](https://img.shields.io/github/release/Happyr/auth0-bundle.svg?style=flat-square)](https://github.com/Happyr/auth0-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/auth0-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/auth0-bundle)

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

Add your credentials:

```yaml
// config/packages/happyr_auth0.yml
happyr_auth0:
    domain: '%env(AUTH0_DOMAIN)%'
    login_domain: '%env(AUTH0_LOGIN_DOMAIN)%'
    client_id: '%env(AUTH0_CLIENT_ID)%'
    client_secret: '%env(AUTH0_SECRET)%'
    cache: 'cache.redis'
    scope: openid profile email # "openid" is required.
    # If you want to configure firewall, then this section is required
    firewall:
        check_route: default_login_check
        failure_path: default_logout
        default_target_path: user_dashboard
```

Configure your application's security:

```yaml
// config/packages/security.yml
security:
    enable_authenticator_manager: true # Use the new authentication system
    providers:
        users:
            entity:
                class: 'App\Entity\User'
                property: 'auth0Id'

    firewalls:
        default:
            pattern:  ^/.*

            entry_point: auth0.entry_point
            custom_authenticators:
                - auth0.authenticator

            logout:
                path: default_logout
                target: _user_logout
                invalidate_session: true
```

## Custom user provider

If you want to use a custom user provider that fetches a user with more data than
just the Auth0 id, then you may create a service that implement `Happyr\Auth0Bundle\Security\Auth0UserProviderInterface`.

Then configure the bundle to use that service:

```yaml
// config/packages/happyr_auth0.yml
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
