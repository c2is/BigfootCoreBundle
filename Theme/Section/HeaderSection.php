<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class HeaderSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class HeaderSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'header';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        $themeValues = $this->container->getParameter('bigfoot.theme.values');
        $this->parameters = array(
            'title'    => $themeValues['title'],
            'subtitle' => $themeValues['subtitle'],
        );
    }
}
