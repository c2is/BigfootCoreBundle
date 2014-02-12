<?php

namespace Bigfoot\Bundle\CoreBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BigfootCoreBundle
 * @package Bigfoot\Bundle\CoreBundle
 */
class BigfootCoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->container->get('bigfoot_core.manager.route')->addBundle($this->getName());
    }
}
