<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class PageContentSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class PageContentSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'page_content';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        // $menu = $this->container->get('bigfoot.menu_factory')->getMenu('actions_menu');

        $this->parameters = array(
            'title'         => '',
            // 'globalActions' => $menu,
        );
    }
}
