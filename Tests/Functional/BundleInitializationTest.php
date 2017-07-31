<?php

namespace Happyr\Auth0Bundle\Tests\Functional;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Happyr\Auth0Bundle\HappyrAuth0Bundle;
use Happyr\Auth0Bundle\Security\EntryPoint\SSOEntryPoint;
use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return HappyrAuth0Bundle::class;
    }

    public function testInitBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config/default.yml');
        $kernel->addBundle(FrameworkBundle::class);
        $kernel->addBundle(SecurityBundle::class);
        $this->bootKernel();
        $container = $this->getContainer();

        // Test if you services exists
        $map = [
            'happyr.auth0.api.authentication' => Authentication::class,
            'happyr.auth0.api.management' => Management::class,
            'happyr.auth0.security.authentication.entry_point.sso.default' => SSOEntryPoint::class,
        ];

        foreach ($map as $serviceId => $class) {
            $this->assertTrue($container->has($serviceId));
            $service = $container->get($serviceId);
            $this->assertInstanceOf($class, $service);
        }
    }
}
