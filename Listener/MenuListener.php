<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;

class MenuListener
{
    public function onMenuGenerate(MenuEvent $event)
    {
        $menu = $event->getMenu();

        if ($menu->getName() == 'sidebar_menu')
        {
            if ($settings = $menu->getItem('sidebar_settings')) {
                $settings->setLabel('Settings');
            } else {
                $menu->addItem(new Item('sidebar_settings', 'Settings'));
            }
        }
    }
}