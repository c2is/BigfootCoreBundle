<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Entity\QuickLink;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * QuickLink Controller
 * @Route("/quicklink")
 */
class QuickLinkController extends CrudController
{

    protected function getName()
    {
        return 'admin_quicklink_form';
    }

    /**
     * Must return the entity full name (eg. BigfootCoreBundle:Tag).
     *
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootCoreBundle:QuickLink';
    }

    /**
     * Must return an associative array field name => field label.
     *
     * @return array
     */
    protected function getFields()
    {
        return array(
            'id'        => 'ID',
            'linkLabel' => 'linkLabel'
        );
    }

    protected function getFormType()
    {
        return 'bigfoot_bundle_corebundle_quicklinktype';
    }

    /**
     * QuickLink Widget
     *
     * @Route("/widget", name="admin_quicklink_widget")
     * @Template("BigfootCoreBundle:quicklink:quicklink.widget.html.twig")
     */
    public function quickLinkWidgetAction()
    {
        $em = $this->getEntityManager();
        $quickLinks = $em->getRepository('BigfootCoreBundle:QuickLink')->findBy(array(),array('id' => 'desc'));

        return array(
            'quicklinks' => $quickLinks
        );
    }

    /**
     * QuickLink Star
     *
     * @Route("/star/{currentRoute}", name="admin_quicklink_star")
     * @Template("BigfootCoreBundle:quicklink:quicklink.star.html.twig")
     */
    public function quickLinkStarAction($currentRoute)
    {
        $em = $this->getEntityManager();
        $quickLink = $em->getRepository('BigfootCoreBundle:QuickLink')->findOneByLink($currentRoute);

        $alreadyQuickLink = false;

        if ($quickLink) {
            $alreadyQuickLink = true;
        }

        return array(
            'alreadyQuickLink' => $alreadyQuickLink
        );
    }

    /**
     * QuickLink form.
     *
     * @Route("/", name="admin_quicklink_form")
     * @Template("BigfootCoreBundle:quicklink:quicklink.form.html.twig")
     */
    public function quickLinkFormAction()
    {
        $form   = $this->createForm('bigfoot_bundle_corebundle_quicklinktype', new QuickLink());

        return array(
            'formQuickLink' => $form->createView()
        );
    }

    /**
     * QuickLink form.
     *
     * @Route("/new", name="admin_quicklink_form_new")
     * @Template("BigfootCoreBundle:quicklink:popin.quicklink.html.twig")
     */
    public function newAction()
    {
        $arrayNew = $this->doNew($this->getRequest());
        $arrayNew['isAjax'] = true;
        $arrayNew['modal_title'] = 'bigfoot_core.quick_link.modal.title';

        return $arrayNew;
    }

    /**
     * Creates a new QuickLink entity.
     *
     * @Route("/create", name="admin_quicklink_form_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:quicklink:popin.quicklink.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new QuickLink();
        $form = $this->container->get('form.factory')->create($this->getFormType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($entity);
            $em->flush();

            return new JsonResponse(array(
                'success' => true
            ));
        }

        return new JsonResponse(array(
            'success' => false,
            'errors'  => 'Form is invalid',
        ));
    }

    /**
     * Deletes a QuickLink entity.
     *
     * @Route("/delete/{id}", name="admin_quicklink_delete")
     * @Method("GET|DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
