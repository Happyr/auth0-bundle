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
     * @param $id
     * @param array $params
     *
     * @return mixed|ResponseInterface
     */
    public function update($id, array $params = [])
    {
        // TODO wrote me
        if (empty($id)) {
            throw new InvalidArgumentException('User id cannot be empty');
        }

        $default = [
            'client_id' => $this->clientData->getId(),
        ];

        $response = $this->httpPost('/api/v2/users/'.$id, array_merge($default, $params));

        return $this->hydrateResponse($response, TokenModel::class);
    }
}
