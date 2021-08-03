<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\DependencyInjection;

use Auth0\SDK\Configuration\SdkConfiguration;
use Happyr\Auth0Bundle\Security\Auth0EntryPoint;
use Happyr\Auth0Bundle\Security\Auth0UserProviderInterface;
use Happyr\Auth0Bundle\Security\Authentication\Auth0Authenticator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $this->configureSdkConfiguration($configuration, $container, $config['sdk'] ?? []);
        $this->configureFirewall($container, $config['firewall'] ?? []);
    }

    private function configureSdkConfiguration(Configuration $configuration, ContainerBuilder $container, array $config)
    {
        $sdkConfigurationDefinition = $container->getDefinition(SdkConfiguration::class);
        $sdkConfigurationDefinition->setArgument('$configuration', null);

        foreach ($config as $key => $value) {
            if (null !== $value && $configuration->isArgumentObject($key)) {
                $value = new Reference($value);
            }

            $sdkConfigurationDefinition->setArgument('$'.$key, $value);
        }
    }

    private function configureFirewall(ContainerBuilder $container, array $config)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(Auth0Authenticator::class);
            $container->removeDefinition(Auth0EntryPoint::class);

            return;
        }

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

        $def = $container->getDefinition(Auth0EntryPoint::class);
        $def->setArgument('$loginCheckRoute', $config['check_route']);
        $def->setArgument('$loginDomain', $config['login_domain'] ?? null);

        $def = $container->getDefinition(Auth0Authenticator::class);
        $def->setArgument('$loginCheckRoute', $config['check_route']);
        $def->addTag('container.service_subscriber', ['key' => AuthenticationFailureHandlerInterface::class, 'id' => $failureHandler]);
        $def->addTag('container.service_subscriber', ['key' => AuthenticationSuccessHandlerInterface::class, 'id' => $successHandler]);

        if (!empty($config['user_provider'])) {
            $def->addTag('container.service_subscriber', ['key' => Auth0UserProviderInterface::class, 'id' => $config['user_provider']]);
        }
    }
}
