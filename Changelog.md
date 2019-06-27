# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release. 

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
