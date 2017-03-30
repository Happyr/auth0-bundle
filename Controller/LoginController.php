<?php

namespace Happyr\Auth0Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LoginController extends Controller
{
    public function loginAction()
    {
        // DO
        //https://happyr.eu.auth0.com/authorize?client_id=ApXTjvbi2UYq8cJIrGaaBWZgqYfgsL5I&response_type=code&redirect_uri=https://happyr.com/auth0/callback
        // Ref
        //https://happyr.eu.auth0.com/authorize?client_id=ApXTjvbi2UYq8cJIrGaaBWZgqYfgsL5I&response_type=code|token&connection=CONNECTION&redirect_uri=https://happyr.com/auth0/callback&state=STATE
    }
}
