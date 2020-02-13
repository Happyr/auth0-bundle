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

    public function setAuth0Data(Token $data)
    {
        $this->auth0Data = $data;
    }

    public function getAuth0Data(): ?Token
    {
        return $this->auth0Data;
    }

    public function getAccessToken(): ?string
    {
        if (null === $this->auth0Data) {
            return null;
        }

        return $this->auth0Data->getAccessToken();
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        if (null === $this->auth0Data) {
            return null;
        }

        return $this->auth0Data->getExpiresAt();
    }

    public function getUserModel(): ?UserInfo
    {
        return $this->userModel;
    }

    public function setUserModel(UserInfo $userModel)
    {
        $this->userModel = $userModel;
    }

    public function getCredentials()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        $user = $this->getUser();

        return [
            is_object($user) ? clone $user : $user,
            is_object($this->userModel) ? clone $this->userModel : $this->userModel,
            $this->isAuthenticated(),
            $this->getRoles(),
            $this->getAttributes(),
            $this->auth0Data,
        ];

    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$user, $this->userModel, $isAuthenticated, $this->storedRoles, $attributes, $auth0Data] = $data;
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
     *
     * @deprecated
     */
    public function getRoles()
    {
        // To avoid any Symfony deprecation notices created by Symfony
        if (0 === \func_num_args()) {
            $parentRoles = parent::getRoles();
        } else {
            $parentRoles = parent::getRoles(func_get_arg(0));
        }

        $allRoles = array_merge($parentRoles, $this->storedRoles);
        $uniqueRoles = [];

        /** @var Role $role */
        foreach ($allRoles as $role) {
            $uniqueRoles[$role->getRole()] = $role;
        }

        return array_values($uniqueRoles);
    }
}
