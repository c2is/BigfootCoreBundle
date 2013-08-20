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
        $this->container->get('bigfoot.route_manager')->addBundle($this->getName());

        Type::addType('point', 'Bigfoot\Bundle\CoreBundle\ORM\PointType');
        $em = $this->container->get('doctrine.orm.bigfoot_entity_manager');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
    }
}
