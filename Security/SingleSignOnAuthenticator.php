<?php

namespace Happyr\Auth0Bundle\Security;

use Auth0\SDK\API\Authentication;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SingleSignOnAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var Authentication
     */
    private $api;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $auth0ClientId;

    /**
     * @var string
     */
    private $auth0Domain;

    /**
     * @var string
     */
    private $auth0Connection;

    /**
     *
     * @param Authentication $api
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $auth0ClientId
     * @param string $auth0Domain
     * @param string $auth0Connection
     */
    public function __construct(
        Authentication $api,
        UrlGeneratorInterface $urlGenerator,
        $auth0ClientId,
        $auth0Domain,
        $auth0Connection
    ) {
        $this->api = $api;
        $this->urlGenerator = $urlGenerator;
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0Domain = $auth0Domain;
        $this->auth0Connection = $auth0Connection;
    }


    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        // TODO
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            // no token? Return null and no other methods will be called
            return;
        }

        // What you return here will be passed to getUser() as $credentials
        return array(
            'token' => $token,
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['token'];

        // if null, authentication will fail
        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($apiKey);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $query = [
            'client_id'=>$this->auth0ClientId,
            'connection'=>$this->auth0Connection,
            'redirect_uri'=>$this->urlGenerator->generate('auth0_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type'=>'code',
            'state'=>$request->getUri(),
        ];

        return new RedirectResponse(sprintf('https://%s/authorize?%s', $this->auth0Domain, http_build_query($query)));
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
