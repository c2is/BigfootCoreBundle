<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

class HeaderSection extends AbstractSection
{
    public function getName()
    {
        return 'header';
    }

    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title'    => $this->container->getParameter('bigfoot.theme.values')['title'],
            'subtitle' => $this->container->getParameter('bigfoot.theme.values')['subtitle'],
        );
    }
}
