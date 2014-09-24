<?php

namespace Bigfoot\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class FormatterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('bigfoot_core.crud.formatter.loader')) {
            return;
        }

        $definition     = $container->getDefinition('bigfoot_core.crud.formatter.loader');
        $taggedServices = $container->findTaggedServiceIds('bigfoot.crud.formatter');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addFormatter',
                array(new Reference($id), $id)
            );
        }
    }
}
