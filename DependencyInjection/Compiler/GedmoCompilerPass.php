<?php

namespace Bigfoot\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Gedmo compiler pass
 */
class GedmoCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!in_array($container->getParameterBag()->get('kernel.environment'), array('admin', 'admin_dev'))) {
            return;
        }

        if ($container->hasDefinition('stof_doctrine_extensions.event_listener.locale')) {
            $listener = $container->getDefinition('stof_doctrine_extensions.event_listener.locale');

            $listener->clearTags();
        }
    }
}
