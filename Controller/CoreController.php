<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * Implements the main actions for the BackOffice.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 */
class CoreController extends BaseController
{
    /**
     * @Route("/", name="admin_home")
     */
    function homeAction()
    {
        return $this->render(sprintf('%s::base.html.twig', $this->container->getParameter('bigfoot.theme.bundle')), array());
    }
}
