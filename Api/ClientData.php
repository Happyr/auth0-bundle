<?php

namespace Happyr\Auth0Bundle\Api;

class ClientData
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $secret;

    /**
     *
     * @param string $id
     * @param string $secret
     */
    public function __construct($id, $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
}
