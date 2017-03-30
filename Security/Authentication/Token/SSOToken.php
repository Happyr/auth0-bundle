<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SSOToken extends AbstractToken
{
    private $accessToken;

    private $expiresAt;

    /**
     *
     * @param $userId
     */
    public function __construct(array $roles = [])
    {
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


    public function getCredentials()
    {
        return '';
    }
}
