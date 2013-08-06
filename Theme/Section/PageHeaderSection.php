<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class PageHeaderSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class PageHeaderSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'page_header';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title'    => '',
        );
    }
}
