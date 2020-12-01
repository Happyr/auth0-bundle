# Auth0 integration with Symfony

[![Latest Version](https://img.shields.io/github/release/Happyr/auth0-bundle.svg?style=flat-square)](https://github.com/Happyr/auth0-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/Happyr/auth0-bundle.svg?style=flat-square)](https://travis-ci.org/Happyr/auth0-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/auth0-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/auth0-bundle)

### Warning

This bundle is in very early development. However, it is being used in production for at least 3 applications.

### Installation

Install with Composer:

```bash
composer require happyr/auth0-bundle auth0/auth0-php:@alpha php-http/message
```

Enable the bundle in AppKernel.php

```php
public function registerBundles()
{
    $bundles = [
        // ...
        new \Happyr\Auth0Bundle\HappyrAuth0Bundle(),
    ];

    return $bundles;
}
```
Add your credentials:

```yaml
// app/config/config.yml
happyr_auth0:
  domain: example.eu.auth0.com
  client_id: my_client_id
  client_secret: my_secret
  cache: 'cache.provider.apc'
```


Configure your application for Singe Sign On (SSO).

```yaml
// app/config/security.yml
security:
  firewalls:
    default:
      pattern:  ^/.*
      entry_point: 'happyr.auth0.security.authentication.entry_point.sso.default'
      auth0_sso:
        check_path: default_login_check
        login_path: user_login
        failure_path: startpage
      provider: default
      anonymous: ~
      logout:
        path:   default_logout
        target: _user_logout
        invalidate_session: true
```
