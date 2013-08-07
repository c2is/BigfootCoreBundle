<?php

namespace Bigfoot\Bundle\CoreBundle\Crud;

use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Crud controller.
 *
 * Helper class facilitating the generation of crud controllers.
 * Uses the getName() method to generate route names. Your controller must implement a route named after the controller's name.
 *
 * Routes used by this helper are calculated from its name (self::getName()) suffixed with the action's name.
 * Actions used are :
 * index: name
 * edit: name_edit
 * create : name_create
 * update : name_update
 *
 * This helper only works for CRUDing entities situated in the Entity directory for which a Type class exists in the Form directory and is named after the entity's name suffixed with Type (eg: for the entity Bigfoot/Bundle/CoreBundle/Entity/Tag and form type Bigfoot/Bundle/CoreBundle/Form/TagType)
 */
abstract class CrudController extends Controller
{
    /**
     * @var string The bundle name, calculated from getEntity().
     */
    private $bundleName;

    /**
     * @var string The entity name, calculated from getEntity().
     */
    private $entityName;

    /**
     * Used to generate route names.
     * The helper method of this class will use routes named after this name.
     * This means if you extend this class and use its helper methods, if getName() returns 'my_controller', you must implement a route named 'my_controller'.
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * Must return the entity full name (eg. BigfootCoreBundle:Tag).
     *
     * @return string
     */
    abstract protected function getEntity();

    /**
     * Must return an associative array field name => field label.
     *
     * @return array
     */
    abstract protected function getFields();

    /**
     * @return string
     */
    protected function getBundleName()
    {
        if (!$this->bundleName) {
            $names = $this->getBundleAndEntityName();
            $this->bundleName = $names['bundle'];
        }
        return $this->bundleName;
    }

    /**
     * @return string
     */
    protected function getEntityName()
    {
        if (!$this->entityName) {
            $names = $this->getBundleAndEntityName();
            $this->entityName = $names['entity'];
        }
        return $this->entityName;
    }

    protected function getEntityLabel()
    {
        return $this->getEntityName();
    }

    protected function getEntityLabelPlural()
    {
        return sprintf('%ss', $this->getEntityLabel());
    }

    protected function getFormType()
    {
        $formClass = $this->getEntityTypeClass();

        return new $formClass();
    }

    /**
     * @return string
     */
    protected function getEntityClass()
    {
        $namespace = $this->get('kernel')->getBundle($this->getBundleName())->getNamespace();
        return sprintf('\\%s\\Entity\\%s', $namespace, $this->getEntityName());
    }

    /**
     * @return string
     */
    protected function getEntityTypeClass()
    {
        $namespace = $this->get('kernel')->getBundle($this->getBundleName())->getNamespace();
        return sprintf('\\%s\\Form\\%sType', $namespace, $this->getEntityName());
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getBundleAndEntityName()
    {
        try {
            list($bundleName, $entityName) = explode(':', $this->getEntity());
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Bundle \'%s\' was not found. Make sure your getEntity() method returns the correct value (BundleName:EntityName) and your bundle is correctly registered in your AppKernel.', $bundleName));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Return format of method getEntity() is invalid. Expected a string of format BundleName:EntityName, got %s', $this->getEntity()));
        }

        return array('bundle' => $bundleName, 'entity' => $entityName);
    }

    /**
     * Will be used as a label for the "add new" menu item.
     *
     * @return string
     */
    protected function getAddLabel()
    {
        return 'Add an item';
    }

    /**
     * Helper adding an "add new" menu item into the global actions menu and returning all the entities.
     * Meant to be used in a basic index action.
     *
     * @return array An array containing the entities.
     */
    protected function doIndex()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository($this->getEntity())->findAll();

        if (method_exists($this, 'newAction')) {
            $theme = $this->container->get('bigfoot.theme');
            $theme['page_content']['globalActions']->addItem(new Item('crud_add', $this->getAddLabel(), sprintf('%s_new', $this->getName())));
        }

        return array(
            'entities'              => $entities,
            'edit_route'            => $this->getRouteNameForAction('edit'),
            'entity_label_plural'   => $this->getEntityLabelPlural(),
            'fields'                => $this->getFields(),
        );
    }

    /**
     * Helper inserting a new entity into the database using Doctrine.
     * If the acl provider service is loaded, adds an ACE on the entity with an OWNER mask.
     *
     * If the creation form is not valid, returns an array containing the entity and the form view.
     * If the creation form is valid, redirects to the index action.
     *
     * Meant to be used in a basic create action.
     *
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function doCreate(Request $request)
    {
        $entityClass = $this->getEntityClass();

        $entity = new $entityClass();
        $form = $this->createForm($this->getFormType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {
            if ($this->has('security.acl.provider')) {
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = ObjectIdentity::fromDomainObject($entity);
                $acl = $aclProvider->createAcl($objectIdentity);

                $securityContext = $this->get('security.context');
                $user = $securityContext->getToken()->getUser();
                $securityIdentity = UserSecurityIdentity::fromAccount($user);

                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->getName()));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Helper instantiating a new entity and creating a form.
     *
     * @return array An array containing the entity and the form view.
     */
    protected function doNew()
    {
        $entityClass = $this->getEntityClass();

        $entity = new $entityClass();
        $form   = $this->createForm($this->getFormType(), $entity);

        return array(
            'entity'        => $entity,
            'form'          => $form->createView(),
            'create_route'  => $this->getRouteNameForAction('create'),
            'index_route'   => $this->getRouteNameForAction('index'),
            'entity_label'  => $this->getEntityName(),
        );
    }

    /**
     * Helper creating an edit form for the entity with id $id.
     *
     * @param $id
     * @return array An array containing the entity, the edit form view and the delete form view.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doEdit($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $editForm = $this->createForm($this->getFormType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
            'update_route'  => $this->getRouteNameForAction('update'),
            'index_route'   => $this->getRouteNameForAction('index'),
            'delete_action' => $this->generateUrl($this->getRouteNameForAction('delete'), array('id' => $entity->getId())),
            'entity_label'  => $this->getEntityLabel(),
        );
    }

    /**
     * Helper updating an existing entity into the database using Doctrine.
     *
     * If the edit form is not valid, returns an array containing the entity, the edit form view and the delete form view.
     * If the edit form is valid, redirects to the edit action.
     *
     * Meant to be used in a basic update action.
     *
     * @param Request $request
     * @param int $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doUpdate(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm($this->getFormType(), $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl(sprintf('%s_edit', $this->getName()), array('id' => $id)));
        }

        return array(
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
            'delete_action' => $this->generateUrl($this->getRouteNameForAction('delete'), array('id' => $entity->getId())),
        );
    }

    /**
     * Helper deleting an entity through Doctrine.
     *
     * Redirects to the index action.
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doDelete(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository($this->getEntity())->find($id);

            if (!$entity) {
                throw $this->createNotFoundException(sprintf('Unable to find %s entity.', $this->getEntity()));
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl($this->getName()));
    }

    /**
     * Creates a delete form.
     *
     * @param $id
     * @return \Symfony\Component\Form\Form
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    protected function getRouteNameForAction($action)
    {
        if (!$action or $action == 'index') {
            return $this->getName();
        }

        return sprintf('%s_%s', $this->getName(), $action);
    }
}
