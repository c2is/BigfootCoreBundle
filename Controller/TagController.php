<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Bigfoot\Bundle\CoreBundle\Crud\CrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tag controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/admin/tag")
 */
class TagController extends CrudController
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'admin_tag';
    }

    /**
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootCoreBundle:Tag';
    }

    protected function getFields()
    {
        return array('id' => 'ID', 'name' => 'Name');
    }

    protected function getEntityLabelPlural()
    {
        return 'Tags';
    }

    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="admin_tag")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:index.html.twig")
     */
    public function indexAction()
    {

        return $this->doIndex();
    }
    /**
     * Creates a new Tag entity.
     *
         * @Route("/", name="admin_tag_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
         */
    public function createAction(Request $request)
    {

            return $this->doCreate($request);
        }

    /**
     * Displays a form to create a new Tag entity.
     *
         * @Route("/new", name="admin_tag_new")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
         */
    public function newAction()
    {

            return $this->doNew();
        }

    /**
     * Displays a form to edit an existing Tag entity.
     *
         * @Route("/{id}/edit", name="admin_tag_edit")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
         */
    public function editAction($id)
    {

            return $this->doEdit($id);
        }

    /**
     * Edits an existing Tag entity.
     *
         * @Route("/{id}", name="admin_tag_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
         */
    public function updateAction(Request $request, $id)
    {

            return $this->doUpdate($request, $id);
        }
    /**
     * Deletes a Tag entity.
     *
         * @Route("/{id}", name="admin_tag_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        return $this->doDelete($request, $id);
    }

    /**
     * @Route("/get", name="admin_tag_get")
     */
    function getAction()
    {
        $em = $this->get('doctrine')->getManager();

        $tagRepository = $em->getRepository('BigfootCoreBundle:Tag');
        $tagsToJson = array();
        foreach ($tagRepository->findAll() as $tag) {
            $tagsToJson[] = $tag->getName();
        }

        return new Response(json_encode($tagsToJson), 200, array('Content-Type', 'application/json'));
    }
}
