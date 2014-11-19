<?php

namespace Bigfoot\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Changes the Router implementation.
 *
 */
class SetRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('bigfoot_core.routing.replace_symfony_router') && true === $container->getParameter('bigfoot_core.routing.replace_symfony_router')) {
            $chainRouter = $container->getDefinition('bigfoot_core.cmf_routing.router');

            if ($container->hasParameter('bigfoot_core.routing.routers_by_id')) {
                foreach ($container->getParameter('bigfoot_core.routing.routers_by_id') as $routerId => $priority) {
                    $chainRouter->addMethodCall('add', array(new Reference($routerId), trim($priority)));
                }

                $container->setAlias('router', 'bigfoot_core.cmf_routing.router');
            }
        }
    }
}
