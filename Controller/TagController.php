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
class TagController extends ContainerAware
{
    /**
     * @Route("/admin/tag/get", name="admin_tag_get")
     */
    function homeAction()
    {
        $em = $this->container->get('doctrine')->getManager();

        $tagRepository = $em->getRepository('BigfootCoreBundle:Tag');
        $tagsToJson = array();
        foreach ($tagRepository->findAll() as $tag) {
            $tagsToJson[] = $tag->getName();
        }

        return new Response(json_encode($tagsToJson), 200, array('Content-Type', 'application/json'));
    }
}
