<?php


/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Model\Authentication;

use Happyr\Auth0Bundle\Api\Model\CreatableFromArray;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Token implements CreatableFromArray
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var string
     */
    private $idToken;

    /**
     * @var string
     */
    private $tokenType;

    /**
     * @var int
     */
    private $expiresIn;

    /**
     * @var \DateTimeInterface
     */
    private $expiresAt;

    /**
     * @param string $tokenType
     * @param int    $expiresIn
     */
    public function __construct($tokenType, $expiresIn)
    {
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->expiresAt = (new \DateTimeImmutable())->modify('+'.$expiresIn.' seconds');
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function create(array $data)
    {
        $token = new self($data['token_type'], $data['expires_in']);

        if (isset($data['access_token'])) {
            $token->setAccessToken($data['access_token']);
        }
        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
        }
        if (isset($data['id_token'])) {
            $token->setIdToken($data['id_token']);
        }

        return $token;
    }

    /**
     * @param string $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param string $refreshToken
     */
    private function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @param string $idToken
     ´´     */
    private function setIdToken($idToken)
    {
        $this->idToken = $idToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}
