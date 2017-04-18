<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api\Authentication;

use Happyr\Auth0Bundle\Api\Api\HttpApi;
use Happyr\Auth0Bundle\Api\Exception;
use Happyr\Auth0Bundle\Api\Model\User\UserInfo;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class UserProfile extends HttpApi
{
    /**
     * @return UserInfo|ResponseInterface
     *
     * @throws Exception
     */
    public function userInfo()
    {
        $response = $this->httpGet('/userinfo');

        return $this->hydrateResponse($response, UserInfo::class);
    }
}
