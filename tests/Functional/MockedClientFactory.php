<?php

namespace Happyr\Auth0Bundle\Tests\Functional;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

class MockedClientFactory
{
    public static function create()
    {
        $response = new MockResponse('{
  "access_token":"eyJz93a...k4laUWw",
  "refresh_token":"GEbRxBN...edjnXbL",
  "id_token":"eyJ0XAi...4faeEoQ",
  "token_type":"Bearer",
  "expires_in":86400
}');

        return new Psr18Client(new MockHttpClient([$response]));
    }
}
