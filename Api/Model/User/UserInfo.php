<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Model\User;

use Happyr\Auth0Bundle\Api\Model\CreatableFromArray;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class UserInfo implements CreatableFromArray, \ArrayAccess
{
    /**
     * @var array the raw data from the API.
     */
    private $data;

    /**
     *
     * @param array $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function create(array $data)
    {
        return new self($data);
    }

    /**
     *
     * @return string
     */
    function __toString()
    {
        if (!empty($this->data['email'])) {
            return $this->data['email'];
        }

        if (!empty($this->data['name'])) {
            return $this->data['name'];
        }

        if (!empty($this->data['nickname'])) {
            return $this->data['nickname'];
        }

        return '';
    }


    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('The UserInfo object is read only');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('The UserInfo object is read only');
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->data['email_verified'];
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->data['email'];
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->data['clientID'];
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt()
    {
        return new \DateTimeImmutable($this->data['updated_at']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->data['picture'];
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->data['user_id'];
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->data['nickname'];
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->data['identities'];
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return new \DateTimeImmutable($this->data['created_at']);
    }

    /**
     * @return string
     */
    public function getSub()
    {
        return $this->data['sub'];
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        if (!isset($this->data['roles'])) {
            return [];
        }

        return $this->data['roles'];
    }
}
