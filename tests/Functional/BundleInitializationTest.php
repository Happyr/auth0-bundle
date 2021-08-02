<?php

namespace Happyr\Auth0Bundle\Tests\Functional;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\Configuration\SdkConfiguration;
use Happyr\Auth0Bundle\HappyrAuth0Bundle;
use Happyr\Auth0Bundle\Security\Auth0EntryPoint;
use Happyr\Auth0Bundle\Security\Authentication\Auth0Authenticator;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Nyholm\NSA;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->addCompilerPass(new PublicServicePass('|Auth0\.*|'));
        $this->addCompilerPass(new PublicServicePass('|auth0.*|'));
        $this->addCompilerPass(new PublicServicePass('|Happyr\.*|'));
    }

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

        // Test if the services exists
        $map = [
            Authentication::class => Authentication::class,
            Management::class => Management::class,
            'auth0.entry_point' => Auth0EntryPoint::class,
            'auth0.authenticator' => Auth0Authenticator::class,
        ];

        foreach ($map as $serviceId => $class) {
            $this->assertTrue($container->has($serviceId));
            $service = $container->get($serviceId);
            $this->assertInstanceOf($class, $service);
        }

        // Verify scope
        $service = $container->get(Management::class);
        /** @var SdkConfiguration $config */
        $config = NSA::getProperty($service, 'configuration');
        $this->assertSame('foo bar', $config->buildScopeString());
    }

    public function testExtraConfig()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config/default.yml');
        $kernel->addConfigFile(function (ContainerBuilder $container) {
            $container->loadFromExtension('happyr_auth0', [
                'config' => [
                    'queryUserInfo' => true,
                ],
            ]);
        });
        $kernel->addBundle(FrameworkBundle::class);
        $kernel->addBundle(SecurityBundle::class);

        // This should not throw exception
        $this->bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->has(Management::class));
        $service = $container->get(Management::class);
        $this->assertInstanceOf(Management::class, $service);

        /** @var SdkConfiguration $config */
        $config = NSA::getProperty($service, 'configuration');
        $this->assertTrue($config->getQueryUserInfo());
    }
}
