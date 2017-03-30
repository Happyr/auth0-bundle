<?php

namespace Happyr\Auth0Bundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SSOToken extends AbstractToken
{
    private $authorizationCode;


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
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * @param mixed $authorizationCode
     *
     * @return SSOToken
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }



    public function getCredentials()
    {
        return '';
    }
}
