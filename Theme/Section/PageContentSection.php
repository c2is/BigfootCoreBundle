<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

class PageContentSection extends AbstractSection
{
    public function getName()
    {
        return 'page_content';
    }

    protected function setDefaultParameters()
    {
        $menu = $this->container->get('menu_factory')->getMenu('actions_menu');
        $menuEvent = new MenuEvent($menu);
        $this->container->get('event_dispatcher')->dispatch('bigfoot.menu.generate', $menuEvent);

        $this->parameters = array(
            'title'         => '',
            'globalActions' => $menu,
        );
    }
}
