<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api\Management;

use Happyr\Auth0Bundle\Api\Api\HttpApi;
use Happyr\Auth0Bundle\Api\Exception;
use Happyr\Auth0Bundle\Api\Exception\InvalidArgumentException;
use Happyr\Auth0Bundle\Api\Model\Authentication\Token as TokenModel;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class User extends HttpApi
{
    /**
     * @param string $code
     * @param array  $params
     *
     * @return TokenModel|ResponseInterface
     *
     * @throws Exception
     */
    public function update($id, array $params = [])
    {
        // TODO wrote me
        if (empty($id)) {
            throw new InvalidArgumentException('Code cannot be empty');
        }

        $default = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_secret' => $this->clientData->getSecret(),
            'client_id' => $this->clientData->getId(),
        ];

        $response = $this->httpPost('/oauth/token', array_merge($default, $params));

        return $this->hydrateResponse($response, TokenModel::class);
    }
}
