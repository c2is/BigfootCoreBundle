<?php

namespace Bigfoot\Bundle\CoreBundle\Event;

use Bigfoot\Bundle\CoreBundle\Theme\Menu\Menu;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class MenuEvent
 * @package Bigfoot\Bundle\CoreBundle\Event
 */
class MenuEvent extends Event
{
    /**
     * @var \Bigfoot\Bundle\CoreBundle\Theme\Menu\Menu
     */
    protected $menu;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param $menu
     * @return $this
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }
}