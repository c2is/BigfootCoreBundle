<?php

/*
 * This file is part of the c2is/silex-bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 * (c) Guillaume Manen <g.manen@c2is.fr>
 */

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Core\Theme\Menu\Menu;

class ToolbarSection extends AbstractSection
{
    public function getName()
    {
        return 'toolbar';
    }

    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title' => '',
            'user_menu'    => $this->container->get('menu_factory')->getMenu('user_menu'),
        );
    }
}
