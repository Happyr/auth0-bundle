<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SSOToken extends AbstractToken
{
    private $accessToken;

    private $expiresAt;

    /**
     * The user model for the API
     * @var mixed
     */
    private $userModel;

    /**
     *
     * @param array $userModel
     * @param array $roles
     */
    public function __construct($userModel, array $roles = [])
    {
        $this->userModel = $userModel;
        parent::__construct($roles);
    }

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



    public function getCredentials()
    {
        return '';
    }
}
