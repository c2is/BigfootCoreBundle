<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

/**
 * Class FooterSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
class FooterSection extends AbstractSection
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'footer';
    }

    /**
     * @return mixed|void
     */
    protected function setDefaultParameters()
    {
        $themeValues = $this->container->getParameter('bigfoot.theme.values');
        $this->parameters = array(
            'title' => $themeValues['provided_by'],
        );
    }
}
