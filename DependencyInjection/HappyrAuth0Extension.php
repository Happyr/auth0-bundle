<?php

namespace Happyr\Auth0Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HappyrAuth0Extension extends Extension
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

        // Add the secret key as parameter
        $container->setParameter('auth0.domain', $config['domain']);
        $container->setParameter('auth0.client_id', $config['client_id']);
        $container->setParameter('auth0.client_secret', $config['client_secret']);
        $container->setParameter('auth0.scope', $config['scope']);

        if ($config['cache']) {
            $container->setAlias('auth0.cache', $config['cache']);
        }

        if (!empty($config['httplug_client_service'])) {
            $container->getDefinition('happyr.auth0.api.authentication')
                ->replaceArgument(3, $config['audience'])
                ->replaceArgument(4, $config['scope'])
                ->replaceArgument(5, new Reference($config['httplug_client_service']));

            $container->getDefinition('happyr.auth0.api.management.factory')
                ->replaceArgument(3, new Reference($config['httplug_client_service']));
        }
    }
}
