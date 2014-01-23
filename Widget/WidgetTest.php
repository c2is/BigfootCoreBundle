<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 18:07
 */

namespace Bigfoot\Bundle\CoreBundle\Widget;


use Bigfoot\Bundle\CoreBundle\Model\AbstractWidget;

class WidgetTest extends AbstractWidget
{
    protected function getTemplate()
    {
        return sprintf('%s:includes:base.widget.html.twig', $this->container->getParameter('bigfoot.theme.bundle'));
    }

    public function renderContent()
    {
        return "";
    }
}