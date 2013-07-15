<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

class ToolbarSection extends AbstractSection
{
    public function getName()
    {
        return 'toolbar';
    }

    protected function setDefaultParameters()
    {
        $menu = $this->container->get('menu_factory')->getMenu('toolbar_menu');
        $menuEvent = new MenuEvent($menu);
        $this->container->get('event_dispatcher')->dispatch('bigfoot.menu.generate', $menuEvent);

        $this->parameters = array(
            'title' => '',
            'menu'    => $menu,
        );
    }
}
