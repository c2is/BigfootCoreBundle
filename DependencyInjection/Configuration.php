<?php

namespace Bigfoot\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bigfoot_core');

        $rootNode
            ->children()
                ->scalarNode('sitename')->defaultValue('A sample site')->end()
                ->scalarNode('fixture_path')->defaultValue('src/Resources/data/fixtures')->end()
                ->scalarNode('locale')->defaultValue('fr_FR')->end()
                ->booleanNode('debug')->end()
                ->arrayNode('backend')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('Interface d\'administration Bigfoot')->end()
                        ->scalarNode('path')->end()
                        ->scalarNode('provided_by')->defaultValue('C2iS - Agence Digitale')->end()
                    ->end()
                ->end()
                ->arrayNode('firewalls')
                    ->children()
                        ->arrayNode('admin_secured_area_login')
                            ->children()
                                ->scalarNode('pattern')->defaultValue('^/admin/login$')->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin_secured_area')
                            ->children()
                                ->scalarNode('pattern')->defaultValue('^/admin/')->end()
                                ->arrayNode('form')
                                    ->children()
                                        ->scalarNode('login_path')->defaultValue('/admin/login')->end()
                                        ->scalarNode('check_path')->defaultValue('/admin/login_check')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('logout')
                                    ->children()
                                        ->scalarNode('logout_path')->defaultValue('/admin/logout')->end()
                                        ->scalarNode('target_url')->defaultValue('/admin')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
