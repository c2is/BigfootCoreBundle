<?php

namespace Bigfoot\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BigfootCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('bigfoot.theme.sitename', $config['sitename']);
        $container->setParameter('bigfoot.theme.backend.name', $config['backend']['name']);
        $container->setParameter('bigfoot.theme.backend.provided_by', $config['backend']['provided_by']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $routePaths = array();
        if ($container->hasParameter('bigfoot.routes.paths')) {
            $routePaths = $container->getParameter('bigfoot.routes.paths');
        }
        $container->setParameter('bigfoot.routes.paths', array_merge($routePaths, array('BigfootCoreBundle')));
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('assetic', array('bundles' => array('BigfootCoreBundle')));
        $container->prependExtensionConfig('twig', array('globals' => array('theme' => '@theme')));
    }
}
