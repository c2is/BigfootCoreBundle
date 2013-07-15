<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

class PageHeaderSection extends AbstractSection
{
    public function getName()
    {
        return 'page_header';
    }

    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title'    => '',
        );
    }
}
