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
        $themeValues = $this->container->getParameter('bigfoot.theme.values');
        $this->parameters = array(
            'title' => $themeValues['provided_by'],
        );
    }
}
