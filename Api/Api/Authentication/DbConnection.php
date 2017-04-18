<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api\Authentication;

use Happyr\Auth0Bundle\Api\Api\HttpApi;
use Happyr\Auth0Bundle\Api\Model\Message;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class DbConnection extends HttpApi
{
    /**
     * This will reset the users password. It will send a email to the user with a reset link.
     *
     * @param string $connection
     * @param string $email
     *
     * @return mixed|ResponseInterface
     */
    public function changePassword($connection, $email)
    {
        $params = [
            'client_id' => $this->clientData->getId(),
            'email' => $email,
            'connection' => $connection,
        ];

        $response = $this->httpPost('/dbconnections/change_password', $params);

        return $this->hydrateResponse($response, Message::class);
    }
}
