<?php

/*
 * This file is part of the c2is/silex-bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 * (c) Guillaume Manen <g.manen@c2is.fr>
 */

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
            'title' => $this->container->getParameter('bigfoot.theme.backend.provided_by'),
        );
    }
}
