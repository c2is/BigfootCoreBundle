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
                ->scalarNode('theme')->defaultNull()->end()
                ->scalarNode('date_format')->defaultValue("d/m/Y")->end()
                ->arrayNode('mailer')
                    ->children()
                        ->scalarNode('from')->end()
                    ->end()
                ->end()
                ->arrayNode('languages')
                    ->children()
                        ->arrayNode('back')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('fr', 'en'))
                        ->end()
                        ->arrayNode('front')
                            ->useAttributeAsKey('name')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                ->children()
                                ->scalarNode('label')->isRequired()->end()
                                ->scalarNode('value')->isRequired()->end()
                                ->variableNode('parameters')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
