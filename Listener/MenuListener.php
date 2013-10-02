<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;

/**
 * Adds the settings menu and tags management submenu into the sidebar.
 *
 * Class MenuListener
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class MenuListener
{
    /**
     * @param MenuEvent $event
     */
    public function onMenuGenerate(MenuEvent $event)
    {
        $menu = $event->getMenu();

        if ($menu->getName() == 'sidebar_menu')
        {
            if ($settings = $menu->getItem('sidebar_settings')) {
                $settings->setLabel('Settings');
            } else {
                $settings = new Item('sidebar_settings', 'Settings', null, array(), array(), 'wrench');
                $menu->addItem($settings);
            }

            $tagsMenu = new Item('sidebar_tags', 'Tags', null, array(), array(), 'tags');
            $tagsMenu->addChild(new Item('sidebar_settings_tags_category', 'Categories', 'admin_tag_category', array(), array(), 'sitemap'));
            $tagsMenu->addChild(new Item('sidebar_settings_tags_tag', 'Tags', 'admin_tag', array(), array(), 'tag'));
            $menu->addItem($tagsMenu);
        }
    }
}