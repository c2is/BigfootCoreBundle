<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

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
