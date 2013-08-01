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
        $themeValues = $this->container->getParameter('bigfoot.theme.values');
        $this->parameters = array(
            'title'    => $themeValues['title'],
            'subtitle' => $themeValues['subtitle'],
        );
    }
}
