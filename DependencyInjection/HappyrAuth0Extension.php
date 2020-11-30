<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\DependencyInjection;

use Auth0\SDK\API\Authentication;
use Happyr\Auth0Bundle\Factory\ManagementFactory;
use Happyr\Auth0Bundle\Security\Auth0EntryPoint;
use Happyr\Auth0Bundle\Security\Authentication\Auth0Authenticator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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

        $container->setParameter('auth0.domain', $config['domain']);
        $container->setParameter('auth0.login_domain', $config['login_domain'] ?? $config['domain']);
        $container->setParameter('auth0.client_id', $config['client_id']);
        $container->setParameter('auth0.client_secret', $config['client_secret']);
        $container->setParameter('auth0.scope', $config['scope']);
        $container->setParameter('auth0.audience', $config['audience']);

        if ($config['cache']) {
            $container->setAlias('auth0.cache', $config['cache']);
        }

        if ($config['firewall']['enabled']) {
            $this->configureFirewall($container, $config['firewall']);
        } else {
            $container->removeDefinition(Auth0Authenticator::class);
        }

        if (!empty($config['httplug_client_service'])) {
            $container->getDefinition(Authentication::class)
                ->replaceArgument(5, new Reference($config['httplug_client_service']));

            $container->getDefinition(ManagementFactory::class)
                ->replaceArgument(3, new Reference($config['httplug_client_service']));
        }
    }

    private function configureFirewall(ContainerBuilder $container, array $config)
    {
        if (null === $config['success_handler'] && null === $config['default_target_path']) {
            throw new \LogicException('You need to configure either "happyr_auth0.firewall.default_target_path" or "happyr_auth0.firewall.success_handler"');
        }

        if (null !== $config['success_handler'] && null !== $config['default_target_path']) {
            throw new \LogicException('You cannot configure both "happyr_auth0.firewall.default_target_path" and "happyr_auth0.firewall.success_handler"');
        }

        $container->get(Auth0EntryPoint::class)->replaceArgument('$callbackRoute', $config['check_route']);

        $container->setAlias('auth0.authenticator', Auth0Authenticator::class);
        $def = $container->getDefinition(Auth0Authenticator::class);
        $def->setArgument('$checkRoute', $config['check_route']);
        $def->addTag('container.service_subscriber', ['key'=>AuthenticationFailureHandlerInterface::class, 'id'=>'security.csrf.token_manager']);
        $def->addTag('container.service_subscriber', ['key'=>AuthenticationSuccessHandlerInterface::class, 'id'=>'security.csrf.token_manager']);

        if (!empty($config['user_provider'])) {
            $def->addTag('container.service_subscriber', ['key'=>Auth0UserProviderInterface::class, 'id'=>$config['user_provider']]);
        }
    }

}
