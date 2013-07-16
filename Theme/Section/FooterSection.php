<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

class FooterSection extends AbstractSection
{
    public function getName()
    {
        return 'footer';
    }

    protected function setDefaultParameters()
    {
        $this->parameters = array(
            'title' => $this->container->getParameter('bigfoot.theme.values')['provided_by'],
        );
    }
}
