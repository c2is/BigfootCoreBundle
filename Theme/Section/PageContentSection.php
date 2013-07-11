<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

class PageContentSection extends AbstractSection
{
    public function getName()
    {
        return 'page_content';
    }

    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title'         => '',
            'globalActions'    => $this->container->get('menu_factory')->getMenu('actions'),
        );
    }
}
