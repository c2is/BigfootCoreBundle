<?php

namespace Bigfoot\Bundle\CoreBundle\Event;

use Bigfoot\Bundle\CoreBundle\Theme\Menu\Menu;

use Symfony\Component\EventDispatcher\Event;

class MenuEvent extends Event
{
    protected $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }
}