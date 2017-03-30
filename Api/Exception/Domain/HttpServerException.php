<?php

namespace Happyr\Auth0Bundle\Api\Exception\Domain;

use Happyr\ApiClient\Exception;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class HttpServerException extends \RuntimeException implements Exception
{
    public static function serverError($httpStatus = 500)
    {
        return new self('An unexpected error occurred at Happyr\'s servers. Try again later and contact support of the error sill exists.', $httpStatus);
    }

    public static function networkError(\Exception $previous)
    {
        return new self('Happyr\'s servers was unreachable.', 0, $previous);
    }

    public static function unknownHttpResponseCode($code)
    {
        return new self(sprintf('Unknown HTTP response code ("%d") received from the API server', $code));
    }
}
