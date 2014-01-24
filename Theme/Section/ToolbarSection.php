<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class ToolbarSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class ToolbarSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'toolbar';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        // $menu = $this->container->get('bigfoot.menu_factory')->getMenu('toolbar_menu');

        $this->parameters = array(
            'title' => '',
            // 'menu'    => $menu,
        );
    }
}
