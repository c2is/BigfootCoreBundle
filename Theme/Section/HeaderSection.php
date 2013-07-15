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
            'subtitle' => $this->container->getParameter('bigfoot.theme.sitename'),
            'title'    => $this->container->getParameter('bigfoot.theme.backend.name'),
        );
    }
}
