<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 28/01/14
 * Time: 14:32
 */

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TranslatableLabelController
 * @package Bigfoot\Bundle\CoreBundle\Controller
 *
 * @Route("/translatable_labels")
 */
class TranslatableLabelController extends CrudController
{
    /**
     * {@inheritDoc}
     */
    public function getEntity()
    {
        return "BigfootCoreBundle:TranslatableLabel";
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "admin_translatable_label";
    }

    public function getFields()
    {
        return array("name" => "Identifiant", "value" => "Valeur");
    }

    /**
     * Lists all translatable labels.
     *
     * @Route("/", name="admin_translatable_label")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:index.html.twig")
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Edits an existing Tag entity.
     *
     * @Route("/{id}", name="admin_translatable_label_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->doUpdate($request, $id);
    }

    /**
     * Displays a form to edit an existing Tag entity.
     *
     * @Route("/{id}/edit", name="admin_translatable_label_edit")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function editAction($id)
    {
        return $this->doEdit($id);
    }
}