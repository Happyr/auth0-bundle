<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Model;

use Happyr\Auth0Bundle\Api\Model\ApiResponse;

class Message implements ApiResponse
{
    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public static function create($data)
    {
        return new self($data);
    }
}
