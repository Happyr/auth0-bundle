<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Model\Authorization\Token;

use Happyr\Auth0Bundle\Model\ApiResponse;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Token implements ApiResponse
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

    public function __construct(string $tokenType, int $expiresIn)
    {
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->expiresAt = (new \DateTimeImmutable())->modify('+'.$expiresIn.' seconds');
    }

    public static function create(array $data): Token
    {
        $token = new self($data['token_type'], (int) $data['expires_in']);

        if (isset($data['access_token'])) {
            $token->accessToken = $data['access_token'];
        }
        if (isset($data['refresh_token'])) {
            $token->refreshToken = $data['refresh_token'];
        }
        if (isset($data['id_token'])) {
            $token->idToken = $data['id_token'];
        }

        return $token;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getIdToken(): ?string
    {
        return $this->idToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
