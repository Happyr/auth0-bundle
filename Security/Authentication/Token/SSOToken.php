<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\Role;

class SSOToken extends AbstractToken
{
    private $accessToken;

    private $expiresAt;

    /**
     * @var array
     */
    private $storedRoles = [];

    /**
     * The user model for the API.
     *
     * @var mixed
     */
    private $userModel;

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     *
     * @return SSOToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param mixed $expiresAt
     *
     * @return SSOToken
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * @param mixed $userModel
     *
     * @return SSOToken
     */
    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;

        return $this;
    }

    public function getCredentials()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $user = $this->getUser();

        return serialize(
            [
                is_object($user) ? clone $user : $user,
                is_object($this->userModel) ? clone $this->userModel : $this->userModel,
                $this->isAuthenticated(),
                $this->getRoles(),
                $this->getAttributes(),
                $this->accessToken,
                $this->expiresAt,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($user, $this->userModel, $isAuthenticated, $this->storedRoles, $attributes, $this->accessToken, $this->expiresAt) = unserialize($serialized);
        $this->setAuthenticated($isAuthenticated);
        $this->setAttributes($attributes);
        if ($user) {
            $this->setUser($user);
        }
    }

    public function getRoles()
    {
        $allRoles = array_merge(parent::getRoles(), $this->storedRoles);
        $uniqueRoles = [];

        /** @var Role $role */
        foreach ($allRoles as $role) {
            $uniqueRoles[$role->getRole()] = $role;
        }

        return array_values($uniqueRoles);
    }
}
