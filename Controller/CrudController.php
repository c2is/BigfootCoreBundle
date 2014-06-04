<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Comparison;

use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;
use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Bigfoot\Bundle\CoreBundle\Event\FormEvent;
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
                'class' => 'confirm-action',
                'attributes' => array(
                    'data-confirm-message' => $this->getTranslator()->trans('Are you sure ? The %entity% will be permanently deleted.', array('%entity%' => $this->getEntityLabel())),
                ),
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
                'label'      => 'Add',
                'route'      => $this->getRouteNameForAction('new'),
                'parameters' => array(),
                'icon'       => 'pencil',
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
        $entityClass = ltrim($this->getEntityClass(), '\\');

        $queryBuilder = $this
            ->getContextRepository()
            ->createContextQueryBuilder($entityClass);

        foreach ($this->getFields() as $key => $field) {
            if (isset($field['join'])) {
                $queryBuilder
                    ->leftJoin('e.'.$key, $field['join']);
            }
        }

        $query = $queryBuilder
            ->getQuery()
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
            );

        return $this->renderIndex($query);
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
        $action      = $this->generateUrl($this->getRouteNameForAction('new'), array('layout' => $request->get('layout', null)));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->prePersist($entity, 'new');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'new');

                if (!$request->isXmlHttpRequest()) {
                    $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId()));

                    $this->addSuccessFlash('The %entity% has been created.');

                    return $this->redirect($action);
                } else {
                    return $this->handleSuccessResponse('new', $entity);
                }
            }

            if ($request->isXmlHttpRequest()) {
                return $this->renderAjax(false, 'Error during process!', $this->renderForm($form, $action, $entity)->getContent());
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
        $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId(), 'layout' => $request->get('layout', null)));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->prePersist($entity, 'edit');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'edit');

                if (!$request->isXmlHttpRequest()) {
                    $this->addSuccessFlash('The %entity% has been updated.');

                    return $this->redirect($action);
                } else {
                    return $this->handleSuccessResponse('edit', $entity);
                }
            }

            if ($request->isXmlHttpRequest()) {
                return $this->renderAjax(false, 'Error during process!', $this->renderForm($form, $action, $entity)->getContent());
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
            $this->addSuccessFlash('The %entity% has been deleted.');

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('index')));
        } else {
            return $this->renderAjax(true, 'The entity has beed deleted.');
        }
    }

    /**
     * Render index
     */
    protected function renderIndex($query)
    {
        return $this->render(
            $this->getIndexTemplate(),
            array(
                'list_items'    => $this->getPagination($query, $this->getElementsPerPage()),
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
    protected function renderForm($form, $action, $entity)
    {
        return $this->render(
            $this->getFormTemplate(),
            array(
                'form'        => $form->createView(),
                'form_method' => 'POST',
                'form_title'  => $this->getTranslator()->trans('%entity% creation', array('%entity%' => $this->getEntityLabel())),
                'form_action' => $action,
                'form_submit' => 'Submit',
                'form_cancel' => $this->getRouteNameForAction('index'),
                'entity'      => $entity,
                'layout'      => $this->getRequest()->query->get('layout') ?: '',
            )
        );
    }

    /**
     * Add sucess flash
     */
    protected function addSuccessFlash($message, $route = null)
    {
        $actions = array();

        $actions[] = array(
            'route' => $this->generateUrl($this->getRouteNameForAction('index')),
            'label' => 'Back to the listing',
            'type'  => 'success',
        );

        if ($route) {
            $actions[] = array(
                'route' => $route,
                'label' => $this->getTranslator()->trans('Add a new %entity%', array('%entity%' => $this->getEntityName())),
                'type'  => 'success',
            );
        } else {
            if (method_exists($this, 'newAction')) {
                $actions[] = array(
                    'route' => $this->generateUrl($this->getRouteNameForAction('new')),
                    'label' => $this->getTranslator()->trans('Add a new %entity%', array('%entity%' => $this->getEntityName())),
                    'type'  => 'success',
                );
            }
        }

        $this->addFlash(
            'success',
            $this->renderView(
                $this->getThemeBundle().':admin:flash.html.twig',
                array(
                    'icon'    => 'ok',
                    'heading' => 'Success!',
                    'message' => $this->getTranslator()->trans($message, array('%entity%' => $this->getEntityName())),
                    'actions' => $actions
                )
            )
        );
    }

    /**
     * Handle success response
     */
    protected function handleSuccessResponse($action, $entity = null)
    {
        $action = $this->generateUrl($this->getRouteNameForAction('new'));

        return $this->renderAjax(true, 'Success, please wait...', null, $action);
    }

    /**
     * Pre persit entity
     *
     * @param object $entity entity
     */
    protected function prePersist($entity, $action)
    {
    }

    /**
     * Post flush entity
     *
     * @param object $entity entity
     */
    protected function postFlush($entity, $action)
    {
    }
}
