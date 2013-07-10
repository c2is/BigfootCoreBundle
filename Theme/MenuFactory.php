<?php

namespace Bigfoot\Bundle\CoreBundle\Theme;

use Bigfoot\Bundle\CoreBundle\Theme\Menu\Menu;

class MenuFactory
{
    protected $container;

    public function _construct($container)
    {
        $this->container = $container;
    }

    public function getMenu($name)
    {
        return new Menu($this->container, $name);
    }
}