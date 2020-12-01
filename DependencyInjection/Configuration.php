<?php

namespace Happyr\Auth0Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('happyr_auth0');
        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $root = $treeBuilder->root('happyr_auth0');
        } else {
            $root = $treeBuilder->getRootNode();
        }

        $root
            ->children()
                ->scalarNode('domain')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('login_domain')->defaultNull()->info('If you configured SSO with a custom domain.')->end()
                ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('client_secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('cache')->defaultNull()->end()
                ->scalarNode('httplug_client_service')->defaultNull()->end()
                ->scalarNode('scope')->defaultValue('')->info('Seperated with space')->end()
                ->scalarNode('audience')->defaultNull()->end()
                ->arrayNode('firewall')->canBeEnabled()
                    ->children()
                        ->scalarNode('check_route')->isRequired()->info('The route where the user ends up after authentication. Ie, the callback route.')->cannotBeEmpty()->end()
                        ->scalarNode('failure_path')->defaultNull()->info('The path or route where user is redirected on authentication failure.')->end()
                        ->scalarNode('failure_handler')->defaultNull()->info('A service implementing AuthenticationFailureHandlerInterface.')->end()
                        ->scalarNode('default_target_path')->defaultNull()->info('The path or route where user is redirected on authentication success.')->end()
                        ->scalarNode('success_handler')->defaultNull()->info('A service implementing AuthenticationSuccessHandlerInterface.')->end()
                        ->scalarNode('user_provider')->defaultNull()->info('A service implementing Auth0UserProviderInterface. If none provided, the user provider of the firewall will be used.')->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
