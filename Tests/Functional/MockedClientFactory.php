<?php


namespace Happyr\Auth0Bundle\Tests\Functional;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;

class MockedClientFactory
{
    public static function create()
    {
        $client = new Client();
        $client->addResponse(new Response(200, ['Content-Type'=>'application/json'], '{
  "access_token":"eyJz93a...k4laUWw",
  "refresh_token":"GEbRxBN...edjnXbL",
  "id_token":"eyJ0XAi...4faeEoQ",
  "token_type":"Bearer",
  "expires_in":86400
}'));

        return $client;
    }
}
