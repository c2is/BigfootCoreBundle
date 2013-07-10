<?php

/*
 * This file is part of the c2is/silex-bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 * (c) Guillaume Manen <g.manen@c2is.fr>
 */

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Event\Theme\MenuEvent;

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
