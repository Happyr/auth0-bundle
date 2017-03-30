<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Model;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface CreatableFromArray
{
    /**
     * Create an API response object from the HTTP response from the API server.
     *
     * @param array $data
     *
     * @return self
     */
    public static function createFromArray(array $data);
}
