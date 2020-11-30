<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Model\Authentication\UserProfile;

use Happyr\Auth0Bundle\Model\ApiResponse;

/**
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

    /**
     * @return string
     */
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

    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    public function getClientId(): ?string
    {
        return $this->data['clientID'] ?? null;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['updated_at'] ?? 'now');
    }

    public function getName(): string
    {
        return $this->data['name'] ?? $this->data['nickname'] ?? '';
    }

    public function getPicture(): ?string
    {
        return $this->data['picture'] ?? null;
    }

    public function getNickname(): string
    {
        return $this->data['nickname'] ?? '';
    }

    public function getIdentities(): array
    {
        return $this->data['identities'] ?? [];
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->data['created_at'] ?? 'now');
    }

    /**
     * @return string
     */
    public function getUserId(): ?string
    {
        return $this->data['sub'] ?? $this->data['user_id'] ?? null;
    }

    public function getRoles(): array
    {
        if (!isset($this->data['roles'])) {
            return [];
        }

        return $this->data['roles'];
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
