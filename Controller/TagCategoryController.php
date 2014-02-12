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
 * @Route("/tag/category")
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
        return array(
            'id'   => 'ID',
            'name' => 'Name'
        );
    }

    public function getEntityLabel()
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
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Displays a form to create a new TagCategory entity.
     *
     * @Route("/new", name="admin_tag_category_new")
     */
    public function newAction(Request $request)
    {
        return $this->doNew($request);
    }

    /**
     * Displays a form to edit an existing TagCategory entity.
     *
     * @Route("/{id}/edit", name="admin_tag_category_edit")
     */
    public function editAction(Request $request, $id)
    {
        return $this->doEdit($request, $id);
    }

    /**
     * Deletes a TagCategory entity.
     *
     * @Route("/{id}/delete", name="admin_tag_category_delete")
     * @Method("GET|DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
