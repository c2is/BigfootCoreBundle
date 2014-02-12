<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Query;

use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;
use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Bigfoot\Bundle\UserBundle\Entity\User;

/**
 * Crud controller.
 *
 * Helper class facilitating the generation of crud controllers.
 * Uses the getName() method to generate route names. Your controller must implement a route named after the controller's name.
 *
 * Routes used by this helper are calculated from its name (self::getName()) suffixed with the action's name.
 * Actions used are :
 * index: name
 * new  : name_new
 * edit : name_edit
 *
 * This helper only works for CRUDing entities situated in the Entity directory for which a Type class exists in the Form directory and is named after the entity's name suffixed with Type (eg: for the entity Bigfoot/Bundle/CoreBundle/Entity/Tag and form type Bigfoot/Bundle/CoreBundle/Form/TagType)
 */
abstract class CrudController extends BaseController
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
     * @return string Route to be used as the homepage for this controller
     */
    public function getControllerIndex()
    {
        return $this->getName();
    }

    /**
     * @return string Title to be used in the BackOffice for routes implemented by this controller
     */
    public function getControllerTitle()
    {
        return sprintf('%s admin', $this->getEntityLabelPlural());
    }

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

    /**
     * @return string
     */
    public function getEntityLabel()
    {
        return $this->getEntityName();
    }

    /**
     * @return string
     */
    protected function getEntityLabelPlural()
    {
        return sprintf('%ss', $this->getEntityLabel());
    }

    /**
     * @return object
     */
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
        $namespace = $this->container->get('kernel')->getBundle($this->getBundleName())->getNamespace();
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
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Return format of method getEntity() is invalid. Expected a string of format BundleName:EntityName, got %s', $this->getEntity()));
        }

        return array('bundle' => $bundleName, 'entity' => $entityName);
    }

    /**
     * @return string
     */
    protected function getIndexTemplate()
    {
        return $this->getThemeBundle().':crud:index.html.twig';
    }

    /**
     * @return string
     */
    protected function getFormTemplate()
    {
        return $this->getThemeBundle().':crud:form.html.twig';
    }

    /**
     * Will be used as a label for the "add new" menu item.
     *
     * @return string
     */
    protected function getAddLabel()
    {
        return sprintf('Add %s', $this->getEntityName());
    }

    /**
     * @return string
     */
    public function getRouteNameForAction($action)
    {
        if (!$action or $action == 'index') {
            return $this->getName();
        }

        return sprintf('%s_%s', $this->getName(), $action);
    }

    /**
     * Return array of allowed actions
     *
     * @return array
     */
    protected function getActions()
    {
        $actions = array();

        if (method_exists($this, 'editAction')) {
            $actions['edit'] = array(
                'label' => 'Edit',
                'route' => $this->getRouteNameForAction('edit'),
                'icon'  => 'edit',
                'color' => 'green',
            );
        }

        if (method_exists($this, 'deleteAction')) {
            $actions['delete'] = array(
                'label' => 'Delete',
                'route' => $this->getRouteNameForAction('delete'),
                'icon'  => 'trash',
                'color' => 'red',
            );
        }

        return $actions;
    }

    /**
     * Return array of allowed global actions
     *
     * @return array
     */
    protected function getGlobalActions()
    {
        $globalActions = array();

        if (method_exists($this, 'newAction')) {
            $globalActions['new'] = array(
                'label' => 'Add',
                'route' => $this->getRouteNameForAction('new'),
                'icon'  => 'pencil',
            );
        }

        return $globalActions;
    }

    /**
     * Meant to be used in a basic index action.
     *
     * @return array An array containing the entities.
     */
    protected function doIndex()
    {
        $query = $this
            ->getRepository($this->getEntity())
            ->createQueryBuilder('e')
            ->getQuery()
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );

        return $this->renderIndex($query, 10);
    }

    /**
     * Helper inserting a new entity into the database using Doctrine.
     *
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function doNew(Request $request)
    {
        $entityClass = $this->getEntityClass();
        $entity      = new $entityClass();
        $form        = $this->createForm($this->getFormType(), $entity);
        $action      = $this->generateUrl($this->getRouteNameForAction('new'));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->prePersist($entity);

                $this->persistAndFlush($entity);

                if (!$request->isXmlHttpRequest()) {
                    $this->addSuccessFlash('The %s has been created.');
                }

                return $this->handleResponse($request, $entity, true, 'edit');
            }

            if ($request->isXmlHttpRequest()) {
                return $this->handleResponse($request, $entity, false, 'new', $form);
            }
        }

        return $this->renderForm($form, $action, $entity);
    }

    /**
     * Helper creating an edit form for the entity with id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doEdit(Request $request, $id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $form   = $this->createForm($this->getFormType(), $entity);
        $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId()));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->prePersist($entity);

                $this->persistAndFlush($entity);

                if (!$request->isXmlHttpRequest()) {
                    $this->addSuccessFlash('The %s has been updated.');
                }

                return $this->handleResponse($request, $entity, true, 'edit');
            }

            if ($request->isXmlHttpRequest()) {
                return $this->handleResponse($request, $entity, false, 'edit', $form);
            }
        }

        return $this->renderForm($form, $action, $entity);
    }

    /**
     * Helper deleting an entity through Doctrine.
     *
     * Redirects to the index action.
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doDelete(Request $request, $id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $this->removeAndFlush($entity);

        if (!$request->isXmlHttpRequest()) {
            $this->addSuccessFlash('The %s has been deleted.');
        }

        return $this->handleResponse($request, null, true, 'index');
    }

    /**
     * Handle response
     */
    protected function handleResponse(Request $request, $entity = null, $status, $actionName = null, $form = null)
    {
        $action = $this->getActionByName($entity, $actionName);

        if ($request->isXmlHttpRequest() && $status == true) {
            return $this->handleSuccessResponse($entity, $action);
        } elseif ($request->isXmlHttpRequest() && $status == false) {
            return $this->renderAjax(false, 'Error during process!', $this->renderForm($form, $action)->getContent());
        } elseif ($status == true) {
            return $this->redirect($action);
        }
    }

    /**
     * Handle success response
     */
    protected function handleSuccessResponse($entity = null, $action = null)
    {
        return $this->renderAjax(true, 'Success, please wait...', null, $action);
    }

    /**
     * Get action by name
     */
    protected function getActionByName($entity = null, $name)
    {
        if ($name == 'new') {
            return $action = $this->generateUrl($this->getRouteNameForAction('new'));
        } elseif ($name == 'edit') {
            return $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId()));
        } else {
            return $action = $this->generateUrl($this->getRouteNameForAction('index'));
        }
    }

    /**
     * Render index
     */
    protected function renderIndex($query, $nbrPerPage)
    {
        return $this->render(
            $this->getIndexTemplate(),
            array(
                'list_items'    => $this->getPagination($query, 10),
                'list_title'    => $this->getEntityLabelPlural(),
                'list_fields'   => $this->getFields(),
                'actions'       => $this->getActions(),
                'globalActions' => $this->getGlobalActions(),
            )
        );
    }

    /**
     * Render form
     */
    protected function renderForm($form, $action, $entity = null)
    {
        return $this->render(
            $this->getFormTemplate(),
            array(
                'form'         => $form->createView(),
                'form_method'  => 'POST',
                'form_title'   => sprintf('%s creation', $this->getEntityLabel()),
                'form_action'  => $action,
                'form_submit'  => 'Submit',
                'modal'        => ($this->getRequest()->query->get('modal')) ?: false,
                'cancel_route' => $this->getRouteNameForAction('index'),
            )
        );
    }

    /**
     * Add sucess flash
     */
    protected function addSuccessFlash($message)
    {
        $this->addFlash(
            'success',
            $this->renderView(
                $this->getThemeBundle().':layout:flash.html.twig',
                array(
                    'icon'    => 'ok',
                    'heading' => 'Success!',
                    'message' => sprintf($message, $this->getEntityName()),
                    'actions' => array(
                        array(
                            'route' => $this->generateUrl($this->getRouteNameForAction('index')),
                            'label' => 'Back to the listing',
                            'type'  => 'success',
                        ),
                        array(
                            'route' => $this->generateUrl($this->getRouteNameForAction('new')),
                            'label' => sprintf('Add a new %s', $this->getEntityName()),
                            'type'  => 'success',
                        )
                    )
                )
            )
        );
    }

    /**
     * Pre persit entity
     *
     * @param object $entity entity
     */
    protected function prePersist($entity)
    {

    }
}
