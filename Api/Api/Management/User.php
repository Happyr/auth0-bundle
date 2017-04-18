<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api\Management;

use Happyr\Auth0Bundle\Api\Api\HttpApi;
use Happyr\Auth0Bundle\Api\Exception;
use Happyr\Auth0Bundle\Api\Exception\InvalidArgumentException;
use Happyr\Auth0Bundle\Api\Model\Management\User as UserModel;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class User extends HttpApi
{
    /**
     * Update a user. Ie user email, password or email_verified.
     * @param $userId
     * @param array $params
     *
     * @return UserModel|ResponseInterface
     *
     * @throws Exception
     */
    public function update($userId, array $params = [])
    {

        if (empty($userId)) {
            throw new InvalidArgumentException('User id cannot be empty');
        }

        $default = [
            'client_id' => $this->clientData->getId(),
        ];

        $response = $this->httpPost('/api/v2/users/'.$userId, array_merge($default, $params));

        return $this->hydrateResponse($response, UserModel::class);
    }
}
