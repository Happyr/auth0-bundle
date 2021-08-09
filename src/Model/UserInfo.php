<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Model;

/**
 * @see https://auth0.com/docs/users/user-profile-structure
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class UserInfo implements \ArrayAccess
{
    /**
     * @var array the raw data from the API
     */
    private $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(array $data): UserInfo
    {
        return new self($data);
    }

    public function __toString()
    {
        if (!empty($this->data['email'])) {
            return (string) $this->data['email'];
        }

        if (!empty($this->data['name'])) {
            return (string) $this->data['name'];
        }

        if (!empty($this->data['nickname'])) {
            return (string) $this->data['nickname'];
        }

        return '';
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('The UserInfo object is read only');
    }

    public function offsetUnset($offset): void
    {
        throw new \LogicException('The UserInfo object is read only');
    }

    public function isEmailVerified(): bool
    {
        return $this->data['email_verified'] ?? false;
    }

    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    public function gePhoneNumber(): ?string
    {
        return $this->data['phone_number'] ?? null;
    }

    public function isBlocked(): ?bool
    {
        return $this->data['blocked'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    public function getClientId(): ?string
    {
        return $this->data['clientID'] ?? null;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return isset($this->data['updated_at']) ? new \DateTimeImmutable($this->data['updated_at']) : null;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? $this->data['nickname'] ?? null;
    }

    public function getGivenName(): ?string
    {
        return $this->data['given_name'] ?? null;
    }

    public function getFamilyName(): ?string
    {
        return $this->data['family_name'] ?? null;
    }

    public function getPicture(): ?string
    {
        return $this->data['picture'] ?? null;
    }

    public function getNickname(): ?string
    {
        return $this->data['nickname'] ?? null;
    }

    public function getLastIp(): ?string
    {
        return $this->data['last_ip'] ?? null;
    }

    public function getMultifactor(): ?string
    {
        return $this->data['multifactor'] ?? null;
    }

    public function getLoginsCount(): int
    {
        return $this->data['logins_count'] ?? 0;
    }

    public function getIdentities(): array
    {
        return $this->data['identities'] ?? [];
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return isset($this->data['created_at']) ? new \DateTimeImmutable($this->data['created_at']) : null;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return isset($this->data['last_login']) ? new \DateTimeImmutable($this->data['last_login']) : null;
    }

    public function getLastPasswordResetAt(): ?\DateTimeInterface
    {
        return isset($this->data['last_password_reset']) ? new \DateTimeImmutable($this->data['last_password_reset']) : null;
    }

    /**
     * This is the user id from Auth0. If the user is logged in with a social provider
     * this could be null.
     */
    public function getUserId(): ?string
    {
        return $this->data['user_id'] ?? null;
    }

    /**
     * This is a unique id for this user or login method. An Auth0 user may have
     * multiple "login identifiers". So this is "more unique" than the user id.
     */
    public function getLoginIdentifier(): string
    {
        return $this->getUserId() ?? $this->data['sub'];
    }

    public function getRoles(): array
    {
        return $this->data['roles'] ?? [];
    }

    public function getAppMetadata(string $name, $default = null)
    {
        if (!isset($this->data['app_metadata'])) {
            return $default;
        }

        if (!array_key_exists($name, $this->data['app_metadata'])) {
            return $default;
        }

        return $this->data['app_metadata'][$name];
    }

    public function getUserMetadata(string $name, $default = null)
    {
        if (!isset($this->data['user_metadata'])) {
            return $default;
        }

        if (!array_key_exists($name, $this->data['user_metadata'])) {
            return $default;
        }

        return $this->data['user_metadata'][$name];
    }
}
