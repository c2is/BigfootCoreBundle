<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;

/**
 * TagCategory controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/admin/tag/category")
 */
class TagCategoryController extends CrudController
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'admin_tag_category';
    }

    /**
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootCoreBundle:TagCategory';
    }

    protected function getFields()
    {
        return array('id' => 'ID', 'name' => 'Name');
    }

    protected function getEntityLabel()
    {
        return 'Tags category';
    }

    protected function getEntityLabelPlural()
    {
        return 'Tags categories';
    }

    /**
     * Lists all TagCategory entities.
     *
     * @Route("/", name="admin_tag_category")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:index.html.twig")
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Creates a new TagCategory entity.
     *
     * @Route("/", name="admin_tag_category_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->doCreate($request);
    }

    /**
     * Displays a form to create a new TagCategory entity.
     *
     * @Route("/new", name="admin_tag_category_new")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
     */
    public function newAction()
    {
        return $this->doNew();
    }

    /**
     * Displays a form to edit an existing TagCategory entity.
     *
     * @Route("/{id}/edit", name="admin_tag_category_edit")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function editAction($id)
    {
        return $this->doEdit($id);
    }

    /**
     * Edits an existing TagCategory entity.
     *
     * @Route("/{id}", name="admin_tag_category_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->doUpdate($request, $id);
    }
    /**
     * Deletes a TagCategory entity.
     *
     * @Route("/{id}", name="admin_tag_category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
