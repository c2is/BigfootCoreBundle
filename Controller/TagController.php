<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bigfoot\Bundle\CoreBundle\Entity\Tag;
use Bigfoot\Bundle\CoreBundle\Form\TagType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tag controller.
 *
 * @Route("/admin/tag")
 */
class TagController extends Controller
{

    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="admin_tag")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BigfootCoreBundle:Tag')->findAll();

        $theme = $this->container->get('bigfoot.theme');
        $theme['page_content']['globalActions']->addItem(new Item('crud_add', 'Add a tag', 'admin_tag_new'));

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Tag entity.
     *
     * @Route("/", name="admin_tag_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:Tag:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Tag();
        $form = $this->createForm(new TagType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_tag'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Tag entity.
     *
     * @Route("/new", name="admin_tag_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tag();
        $form   = $this->createForm(new TagType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Tag entity.
     *
     * @Route("/{id}/edit", name="admin_tag_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BigfootCoreBundle:Tag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $editForm = $this->createForm(new TagType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Tag entity.
     *
     * @Route("/{id}", name="admin_tag_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:Tag:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BigfootCoreBundle:Tag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new TagType(), $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_tag_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Tag entity.
     *
     * @Route("/{id}", name="admin_tag_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BigfootCoreBundle:Tag')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tag entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_tag'));
    }

    /**
     * Creates a form to delete a Tag entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * @Route("/get", name="admin_tag_get")
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
