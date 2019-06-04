<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Token;

use Happyr\Auth0Bundle\Model\Authentication\UserProfile\UserInfo;
use Happyr\Auth0Bundle\Model\Authorization\Token\Token;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\Role;

class SSOToken extends AbstractToken
{
    /**
     * @var Token|null
     */
    private $auth0Data;

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
     * @param Token $data
     *
     * @return SSOToken
     */
    public function setAuth0Data(Token $data)
    {
        $this->auth0Data = $data;

        return $this;
    }

    /**
     * @return Token|null
     */
    public function getAuth0Data()
    {
        return $this->auth0Data;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        if (null === $this->auth0Data) {
            return null;
        }

        return $this->auth0Data->getAccessToken();
    }

    /**
     * @return mixed
     */
    public function getExpiresAt()
    {
        if (null === $this->auth0Data) {
            return null;
        }

        return $this->auth0Data->getExpiresAt();
    }

    /**
     * @return mixed
     */
    public function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * @param UserInfo $userModel
     *
     * @return SSOToken
     */
    public function setUserModel(UserInfo $userModel)
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
                $this->auth0Data,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($user, $this->userModel, $isAuthenticated, $this->storedRoles, $attributes, $auth0Data) = unserialize($serialized);
        if ($user) {
            $this->setUser($user);
        }

        if ($auth0Data instanceof Token) {
            $this->setAuth0Data($auth0Data);
        }

        $this->setAuthenticated($isAuthenticated);
        $this->setAttributes($attributes);
    }

    public function getRoleNames(): array
    {
        $allRoles = array_merge(parent::getRoleNames(), $this->storedRoles);
        $uniqueRoles = [];

        /** @var Role $role */
        foreach ($allRoles as $role) {
            $name = is_string($role) ? $role : $role->getRole();
            $uniqueRoles[$name] = true;
        }

        return array_keys($uniqueRoles);
    }


    /**
     * This function is deprecated by Symfony 4.3.
     */
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
