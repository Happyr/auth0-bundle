<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\DependencyInjection;

use Happyr\Auth0Bundle\Factory\ConfigurationProvider;
use Happyr\Auth0Bundle\Security\Auth0EntryPoint;
use Happyr\Auth0Bundle\Security\Auth0UserProviderInterface;
use Happyr\Auth0Bundle\Security\Authentication\Auth0Authenticator;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
final class HappyrAuth0Extension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!$config['firewall']['enabled']) {
            $container->removeDefinition(Auth0Authenticator::class);
        } else {
            $entryPointDefinition = $container->getDefinition(Auth0EntryPoint::class);
            $entryPointDefinition->replaceArgument(2, $config['config']['clientId']);
            $entryPointDefinition->replaceArgument(3, $config['login_domain'] ?? $config['domain']);
            $entryPointDefinition->replaceArgument(4, $config['config']['scope']);
            $this->configureFirewall($container, $config['firewall']);
        }

        if (null === $config['config']['httpRequestFactory']) {
            $config['config']['httpRequestFactory'] = new Reference(RequestFactoryInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }
        if (null === $config['config']['httpResponseFactory']) {
            $config['config']['httpResponseFactory'] = new Reference(ResponseFactoryInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }
        if (null === $config['config']['httpStreamFactory']) {
            $config['config']['httpStreamFactory'] = new Reference(StreamFactoryInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }

        $configProviderDefinition = $container->getDefinition(ConfigurationProvider::class);
        $configProviderDefinition->replaceArgument(0, $config['config']);

        if ($config['cache']) {
            $configProviderDefinition->replaceArgument(1, new Reference($config['cache']));
        }

        if (!empty($config['httplug_client_service'])) {
            $configProviderDefinition->replaceArgument(2, new Reference($config['httplug_client_service']));
        }
    }

    private function configureFirewall(ContainerBuilder $container, array $config)
    {
        if (!(null === $config['success_handler'] xor null === $config['default_target_path'])) {
            throw new \LogicException('You must define either "happyr_auth0.firewall.default_target_path" or "happyr_auth0.firewall.success_handler". Exactly one of them, not both.');
        }

        if (!(null === $config['failure_handler'] xor null === $config['failure_path'])) {
            throw new \LogicException('You must define either "happyr_auth0.firewall.failure_path" or "happyr_auth0.firewall.failure_handler". Exactly one of them, not both.');
        }

        if (null === $successHandler = $config['success_handler']) {
            $def = $container->setDefinition($successHandler = 'happyr_auth0.success_handler', new ChildDefinition('security.authentication.success_handler'));
            $def->replaceArgument(1, ['default_target_path' => $config['default_target_path']]);
        }

        if (null === $failureHandler = $config['failure_handler']) {
            $def = $container->setDefinition($failureHandler = 'happyr_auth0.failure_handler', new ChildDefinition('security.authentication.failure_handler'));
            $def->replaceArgument(2, ['failure_path' => $config['failure_path']]);
        }

        $container->getDefinition(Auth0EntryPoint::class)->replaceArgument(5, $config['check_route']);
        $container->setAlias('auth0.entry_point', Auth0EntryPoint::class);

        $container->setAlias('auth0.authenticator', Auth0Authenticator::class);
        $def = $container->getDefinition(Auth0Authenticator::class);
        $def->setArgument('$checkRoute', $config['check_route']);
        $def->addTag('container.service_subscriber', ['key' => AuthenticationFailureHandlerInterface::class, 'id' => $failureHandler]);
        $def->addTag('container.service_subscriber', ['key' => AuthenticationSuccessHandlerInterface::class, 'id' => $successHandler]);

        if (!empty($config['user_provider'])) {
            $def->addTag('container.service_subscriber', ['key' => Auth0UserProviderInterface::class, 'id' => $config['user_provider']]);
        }
    }
}
