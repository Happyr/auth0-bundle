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
        return serialize(
            array(
                is_object($this->userModel) ? clone $this->userModel : $this->userModel,
                $this->accessToken,
                $this->expiresAt
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->userModel, $this->accessToken, $this->expiresAt) = unserialize($serialized);
    }

}
