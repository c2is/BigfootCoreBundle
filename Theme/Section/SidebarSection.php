<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

class SidebarSection extends AbstractSection
{
    public function getName()
    {
        return 'sidebar';
    }

    protected function setDefaultParameters()
    {
        $menu = $this->container->get('menu_factory')->getMenu('Sidebar');

        $this->parameters = array(
            'menu'  => $menu,
        );
    }
}
