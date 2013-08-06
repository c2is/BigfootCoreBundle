<?php

namespace Bigfoot\Bundle\CoreBundle\Theme;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Menu;
use Symfony\Component\DependencyInjection\Container;

/**
 * Creates Menu instances.
 *
 * Class MenuFactory
 * @package Bigfoot\Bundle\CoreBundle\Theme
 */
class MenuFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function _construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Instanciantes a Menu object with the given name.
     *
     * Throws a bigfoot.menu.generate event to allow bundles to manipulate the newly created menu.
     *
     * @param $name
     * @return Menu
     */
    public function getMenu($name)
    {
        $menu = new Menu($this->container, $name);

        $menuEvent = new MenuEvent($menu);
        $this->container->get('event_dispatcher')->dispatch('bigfoot.menu.generate', $menuEvent);

        return $menu;
    }
}