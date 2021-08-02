<?php

namespace Happyr\Auth0Bundle\DependencyInjection;

use Auth0\SDK\Configuration\SdkConfiguration;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $this->addAuth0SdkConfiguration($root);

        $root
            ->children()
                ->scalarNode('cache')->defaultNull()->end()
                ->scalarNode('httplug_client_service')->defaultNull()->end()
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

    private function addAuth0SdkConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $sdkConfigurationRefClass = new \ReflectionClass(SdkConfiguration::class);
        $constructor = $sdkConfigurationRefClass->getConstructor();

        $docBlockFactory = DocBlockFactory::createInstance();
        $block = $docBlockFactory->create($constructor->getDocComment());
        $params = $block->getTagsByName('param');

        $sdkNode = $rootNode
            ->children()
                ->arrayNode('sdk')
                    ->children();

        /** @var Param $param */
        foreach ($params as $param) {
            if ($param->getVariableName() === 'configuration') {
                continue;
            }

            $sdkNode
                ->scalarNode($param->getVariableName())
                ->defaultValue($this->getDefaultValue($param->getVariableName(), $constructor->getParameters()))
                ->info($param->getDescription());
        }
    }

    /**
     * @param string $parameterName
     * @param array<\ReflectionParameter> $parameters
     */
    private function getDefaultValue(string $parameterName, array $parameters): mixed
    {
        foreach ($parameters as $parameter) {
            if ($parameter->getName() === $parameterName) {
                if (!$parameter->isOptional()) {
                    return null;
                }

                return $parameter->getDefaultValue();
            }
        }

        return null;
    }
}
