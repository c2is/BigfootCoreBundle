<?php

namespace Bigfoot\Bundle\CoreBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Bigfoot\Bundle\CoreBundle\DependencyInjection\Compiler\FormatterCompilerPass;
use Bigfoot\Bundle\CoreBundle\DependencyInjection\Compiler\GedmoCompilerPass;

/**
 * Class BigfootCoreBundle
 * @package Bigfoot\Bundle\CoreBundle
 */
class BigfootCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FormatterCompilerPass());
        $container->addCompilerPass(new GedmoCompilerPass());
    }
}
