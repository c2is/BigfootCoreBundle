<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Route controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/route_manager")
 */
class RouteController extends CrudController
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'bigfoot_route';
    }

    /**
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootCoreBundle:Route';
    }

    protected function getFields()
    {
        return array(
            'id'   => array(
                'label' => 'ID',
            ),
            'url'   => array(
                'label' => 'Url',
            ),
            'type' => array(
                'label' => 'Type',
            )
        );
    }

    protected function getFormType()
    {
        return 'bigfoot_bundle_corebundle_routetype';
    }

    /**
     * Lists Route entities.
     *
     * @Route("/", name="bigfoot_route")
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        return $this->doIndex($request);
    }

    /**
     * New Route entity.
     *
     * @Route("/new", name="bigfoot_route_new")
     */
    public function newAction(Request $request)
    {
        return $this->doNew($request);
    }

    /**
     * Edit Route entity.
     *
     * @Route("/edit/{id}", name="bigfoot_route_edit")
     */
    public function editAction(Request $request, $id)
    {
        return $this->doEdit($request, $id);
    }

    /**
     * Delete Route entity.
     *
     * @Route("/delete/{id}", name="bigfoot_route_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
