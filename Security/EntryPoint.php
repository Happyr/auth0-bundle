<?php

namespace Happyr\Auth0Bundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class EntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var HttpKernelInterface
     */
    protected $httpKernel;

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var string
     */
    protected $loginPath;

    /**
     * @var bool
     */
    protected $useForward;

    /**
     * @param HttpKernelInterface $kernel
     * @param HttpUtils           $httpUtils
     * @param string              $loginPath
     * @param bool                $useForward
     */
    public function __construct(HttpKernelInterface $kernel, HttpUtils $httpUtils, $loginPath, $useForward = false)
    {
        $this->httpKernel = $kernel;
        $this->httpUtils = $httpUtils;
        $this->loginPath = $loginPath;
        $this->useForward = (bool) $useForward;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($this->useForward) {
            $subRequest = $this->httpUtils->createRequest($request, $this->loginPath);
            $subRequest->query->add($request->query->getIterator()->getArrayCopy());

            $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            if (200 === $response->getStatusCode()) {
                $response->headers->set('X-Status-Code', 401);
            }

            return $response;
        }

        return $this->httpUtils->createRedirectResponse($request, $this->loginPath);
    }
}
