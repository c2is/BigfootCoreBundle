<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 18:06
 */

namespace Bigfoot\Bundle\CoreBundle\Widget;


use Bigfoot\Bundle\CoreBundle\Model\AbstractWidget;

class SecondTest extends AbstractWidget
{
    protected function getTemplate()
    {
        return sprintf('%s:includes:base.widget.html.twig', $this->container->getParameter('bigfoot.theme.bundle'));
    }

    public function renderContent()
    {
        return 'TOTO4';
    }
}