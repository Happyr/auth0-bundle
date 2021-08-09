# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 0.8.0

### Added

- Updated `UserInfo` to reflect the [Auth0 user model](https://auth0.com/docs/users/user-profile-structure)

### Changed

- [BC break] The `UserInfo::getUserId()` does not use `"sub"` anymore. It goes directly to `"user_id"`. Consider using `UserInfo::getLoginIdentifier()` instead
- [BC break] The configuration has moved around to directly pass config values to the SDK. See readme or dump configuration reference.

### Removed

- [BC break] Removed `ManagementFactory`.
- [BC break] DI container parameters

## 0.7.0

- Removed most things and added support for Symfony 5.2

## 0.6.2

- Better check type in SSOToken::getRoles()

## 0.6.1

- Removed use of deprecated code

## 0.6.0

- Adding Symfony 5 support

## 0.5.3

- Dont warn about deprecation notices when Symfony is calling the getRoles()

## 0.5.2

- Added support for custom SSO domains

## 0.5.1

- Added extra checks so we dont access array keys that do not exist

## 0.5.0

- Added correct language parameter to Universal SSO
- Removed code not used
- Added PHP7.1 type hints
- Removed fluid functions

## 0.4.0

- Set Management to a lazy service
- Added support for Sf 4.3
- Removed support for Sf 2.8
- Removed `UserInfo::getSub()`
- Added scope to `SSOProvicer`

## 0.3.0

- Make sure we can access `Token` from Auth0.
- Make sure `$auth0Data` is actually a `Token`.
- Added options for `scope` and `audience`.

## 0.2.3

- Require 6.0.0-alpha.2

## 0.2.2

- Handle exceptions better

## 0.2.1

- Make sure we do not store an empty access_token in cache
- Added previous exception to SSOProvider
- Fixed deserialisation of isAuthenticated in SSOToken.

## 0.2.0

### Changed

We removed our custom API implementation and started to use the official Auth0 API client.

### Added

We added better Management API
