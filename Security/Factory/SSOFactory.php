<?php

namespace Happyr\Auth0Bundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SSOFactory extends AbstractFactory
{
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'happyr.auth0.security.authentication.provider.sso.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('happyr.auth0.security.authentication.provider.sso'))
            ->replaceArgument(0, new Reference($userProviderId))
        ;

        return $providerId;
    }


    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'happyr.auth0.security.authentication.entry_point.sso.'.$id;

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('happyr.auth0.security.authentication.entry_point.oauth'))
            ->addArgument($config['check_path'])
        ;

        return $entryPointId;
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     * @param $userProvider
     *
     * @return string
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $def = $container->getDefinition($listenerId);
        $def->addMethodCall('setCallbackPath', [$config['check_path']]);

        return $listenerId;
    }


    protected function getListenerId()
    {
        return 'happyr.auth0.security.authentication.listener.sso';
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'auth0_sso';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node->children()
            ->scalarNode('connection')->defaultNull()->end()
        ->end();
    }
}
