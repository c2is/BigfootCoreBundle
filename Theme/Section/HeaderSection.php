<?php

/*
 * This file is part of the c2is/silex-bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 * (c) Guillaume Manen <g.manen@c2is.fr>
 */

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
