<?php

namespace Happyr\Auth0Bundle\Api\Api;

class Authorization extends HttpApi
{
    /**
     * @return Authorization\Token
     */
    public function token()
    {
        return new Authorization\Token($this->httpClient, $this->hydrator, $this->requestBuilder, $this->clientData);
    }
}
