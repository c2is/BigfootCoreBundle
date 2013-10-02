<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Implements the main actions for the BackOffice.
 */
class CoreController extends ContainerAware
{
    /**
     * @Route("/", name="admin_home")
     */
    function homeAction()
    {
        return new Response($this->container->get('twig')->render(sprintf('%s::base.html.twig', $this->container->getParameter('bigfoot.theme.bundle')), array()), 200);
    }
}
