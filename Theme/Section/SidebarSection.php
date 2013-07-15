<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

class SidebarSection extends AbstractSection
{
    public function getName()
    {
        return 'sidebar';
    }

    protected function setDefaultParameters()
    {
        $menu = $this->container->get('menu_factory')->getMenu('sidebar_menu');
        $menuEvent = new MenuEvent($menu);
        $this->container->get('event_dispatcher')->dispatch('bigfoot.menu.generate', $menuEvent);

        $this->parameters = array(
            'menu'  => $menu,
        );
    }
}
