<?php

namespace Happyr\Auth0Bundle\DependencyInjection;

use Auth0\SDK\Configuration\SdkConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    private $argumentObjects = [];

    public function isArgumentObject(string $argument): bool
    {
        return in_array($argument, $this->argumentObjects);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('happyr_auth0');
        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $this->addAuth0SdkConfiguration($root);

        $root
            ->children()
                ->scalarNode('login_domain')->defaultNull()->info('If you configured SSO with a custom domain.')->end()
                ->arrayNode('firewall')->canBeEnabled()
                    ->children()
                        ->scalarNode('check_route')->isRequired()->info('The route where the user ends up after authentication. Ie, the callback route.')->cannotBeEmpty()->end()
                        ->scalarNode('failure_path')->defaultNull()->info('The path or route where user is redirected on authentication failure.')->end()
                        ->scalarNode('failure_handler')->defaultNull()->info('A service implementing AuthenticationFailureHandlerInterface.')->end()
                        ->scalarNode('default_target_path')->defaultNull()->info('The path or route where user is redirected on authentication success.')->end()
                        ->scalarNode('success_handler')->defaultNull()->info('A service implementing AuthenticationSuccessHandlerInterface.')->end()
                        ->scalarNode('user_provider')->defaultNull()->info('A service implementing Auth0UserProviderInterface. If none provided, the user provider of the firewall will be used.')->end()
                        ->scalarNode('target_path_parameter')->defaultValue('_target_path')->info('Name of the query parameter where we store the target path.')->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }

    private function addAuth0SdkConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $sdkNode = $rootNode
            ->children()
                ->arrayNode('sdk')
                    ->info('This node can be configured using key specified in the SDK documentation: https://github.com/auth0/auth0-PHP#configuration-options (only configuration parameter is forbidden).')
                    ->children();

        $sdkConfigurationRefClass = new \ReflectionClass(SdkConfiguration::class);
        $constructor = $sdkConfigurationRefClass->getConstructor();

        foreach ($constructor->getParameters() as $parameter) {
            if ('configuration' === $parameter->getName()) {
                continue;
            }

            switch (true) {
                case $parameter->getType() instanceof \ReflectionNamedType && 'array' === $parameter->getType()->getName():
                    $node = $sdkNode
                        ->arrayNode($parameter->getName())
                                ->scalarPrototype()
                    ;
                    break;
                case $parameter->getType() instanceof \ReflectionNamedType && 'bool' === $parameter->getType()->getName():
                    $node = $sdkNode
                        ->booleanNode($parameter->getName())
                    ;
                    break;
                case $parameter->getType() instanceof \ReflectionNamedType && 'int' === $parameter->getType()->getName():
                    $node = $sdkNode
                        ->integerNode($parameter->getName())
                    ;
                    break;
                default:
                    if ($parameter->getType() instanceof \ReflectionNamedType && !$parameter->getType()->isBuiltin()) {
                        $this->argumentObjects[] = $parameter->getName();
                    }

                    $node = $sdkNode
                        ->scalarNode($parameter->getName())
                    ;
                    break;
            }

            $node->defaultValue($parameter->isOptional() ? $parameter->getDefaultValue() : null);
        }
    }
}
