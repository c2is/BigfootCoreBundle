<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class SidebarSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class SidebarSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'sidebar';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        $menu = $this->container->get('bigfoot.menu_factory')->getMenu('sidebar_menu');

        $this->parameters = array(
            'menu'  => $menu,
        );
    }
}
