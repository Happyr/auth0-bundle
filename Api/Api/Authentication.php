<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api;

use Happyr\Auth0Bundle\Api\Exception;
use Happyr\Auth0Bundle\Api\Exception\InvalidArgumentException;
use Happyr\Auth0Bundle\Api\Model\Stat\Stat as StatModel;
use Happyr\Auth0Bundle\Api\Model\Stat\Total;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Authentication extends HttpApi
{
    /**
     * @param string $username
     * @param array  $params
     *
     * @return StatModel|ResponseInterface
     *
     * @throws Exception
     */
    public function exchangeCodeForToken($code, array $params = [])
    {
        if (empty($code)) {
            throw new InvalidArgumentException('Code cannot be empty');
        }
        if (empty($params['redirect_uri'])) {
            throw new InvalidArgumentException('Redirect uri cannot be empty');
        }

        $state = uniqid();

        $default = [
            'response_type' => 'code',
            'state' => $state,
        ];

        $response = $this->httpGet('authorize', array_merge($default, $params));

        if (!$this->hydrator) {
            return $response;
        }

        // Use any valid status code here
        if ($response->getStatusCode() !== 200) {
            $this->handleErrors($response);
        }

        return $this->hydrator->hydrate($response, StatModel::class);
    }
}
