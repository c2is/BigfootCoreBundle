<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Manager\FilterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Query;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Crud controller.
 *
 * Helper class facilitating the generation of crud controllers.
 * Uses the getName() method to generate route names. Your controller must implement a route named after the
 * controller's name.
 *
 * Routes used by this helper are calculated from its name (self::getName()) suffixed with the action's name.
 * Actions used are :
 * index: name
 * new  : name_new
 * edit : name_edit
 *
 * This helper only works for CRUDing entities situated in the Entity directory for which a Type class exists in the
 * Form directory and is named after the entity's name suffixed with Type (eg: for the entity
 * Bigfoot/Bundle/CoreBundle/Entity/Tag and form type Bigfoot/Bundle/CoreBundle/Form/TagType)
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
     * This means if you extend this class and use its helper methods, if getName() returns 'my_controller', you must
     * implement a route named 'my_controller'.
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
        return $this->getTranslator()->trans(
            'bigfoot_core.crud.controller.default_title',
            array('%entity%' => $this->getEntityLabelPlural())
        );
    }

    /**
     * Get filters
     *
     * @return array
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * Get filter manager
     *
     * @return FilterManager
     */
    protected function getFilterManager()
    {
        return $this->container->get('bigfoot_core.manager.filters');
    }

    /**
     * Get global filters
     *
     * @return mixed
     */
    protected function getGlobalFilters()
    {
        $filters = $this->getFilters();

        if (empty($filters)) {
            return null;
        }

        $datas = array(
            'referer' => $this->getEntity(),
            'fields'  => $filters
        );

        return $datas;
    }

    /**
     * Get full filters
     *
     * @return array
     */
    protected function generateFiltersForm()
    {
        $datas = $this->getGlobalFilters();

        if (empty($datas)) {
            return null;
        }

        $entityName = $this->getEntityName();

        return $this->getFilterManager()->generateFilters($datas, strtolower($entityName));
    }

    /**
     * @return string
     */
    protected function getBundleName()
    {
        if (!$this->bundleName) {
            $names            = $this->getBundleAndEntityName();
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
            $names            = $this->getBundleAndEntityName();
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
     * @return string
     */
    protected function getFormEntityLabel($visibility)
    {
        if (!empty($visibility)) {
            $key = $visibility == 'new' ? 'bigfoot_core.crud.new.title' : 'bigfoot_core.crud.edit.title';

            return $this->getTranslator()->trans($key, array('%entity%' => $this->getEntityLabel()));
        }

        return $this->getTranslator()->trans(
            'bigfoot_core.crud.edit.title',
            array('%entity%' => $this->getEntityLabel())
        );
    }

    /**
     * @param int $countResults
     * @param int $count
     * @param bool $isSearch
     * @return string
     */
    protected function getListEntityLabel($countResults, $count, $isSearch)
    {
        return $this->getEntityLabelPlural();
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Return format of method getEntity() is invalid. Expected a string of format BundleName:EntityName, got %s',
                    $this->getEntity()
                )
            );
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
        $themeSpecificTemplate = sprintf('%s:%s:form.html.twig', $this->getThemeBundle(), $this->getEntityName());
        if ($this->get('templating')->exists($themeSpecificTemplate)) {
            return $themeSpecificTemplate;
        }

        return $this->getThemeBundle().':crud:form.html.twig';
    }

    /**
     * Will be used as a label for the "add new" menu item.
     *
     * @return string
     */
    protected function getAddLabel()
    {
        return $this->getTranslator()->trans(
            'bigfoot_core.crud.new.title',
            array('%entity%' => $this->getEntityName())
        );
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
                'label' => 'bigfoot_core.crud.actions.edit.label',
                'route' => $this->getRouteNameForAction('edit'),
                'icon'  => 'edit',
                'color' => 'green'
            );
        }

        if (method_exists($this, 'duplicateAction')) {
            $actions['duplicate'] = array(
                'label' => 'bigfoot_core.crud.actions.duplicate.label',
                'route' => $this->getRouteNameForAction('duplicate'),
                'icon'  => 'copy',
                'color' => 'green'
            );
        }

        if (method_exists($this, 'deleteAction')) {
            $actions['delete'] = array(
                'label'      => 'bigfoot_core.crud.actions.delete.label',
                'route'      => $this->getRouteNameForAction('delete'),
                'icon'       => 'trash',
                'color'      => 'red',
                'class'      => 'confirm-action',
                'attributes' => array(
                    'data-confirm-message' => $this->getTranslator()->trans(
                        'bigfoot_core.crud.actions.delete.confirm',
                        array('%entity%' => $this->getEntityLabel())
                    ),
                )
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
                'label'      => 'bigfoot_core.crud.actions.new.label',
                'route'      => $this->getRouteNameForAction('new'),
                'parameters' => array(),
                'icon'       => 'pencil',
            );
        }

        return $globalActions;
    }

    /**
     * Get query
     *
     * @return Query
     */
    protected function getQuery()
    {
        $entityClass = ltrim($this->getEntityClass(), '\\');
        $entityName  = $this->getEntityName();

        $queryBuilder = $this
            ->getContextRepository()
            ->createContextQueryBuilder($entityClass);

        foreach ($this->getFields() as $key => $field) {
            if (is_array($field) && isset($field['join'])) {
                $queryBuilder
                    ->leftJoin('e.'.$key, $field['join']);
            }
        }

        $queryBuilder = $this->getFilterManager()->filterQuery(
            $queryBuilder,
            strtolower($entityName),
            $this->getGlobalFilters()
        );

        $this->postQuery($queryBuilder);

        $countQueryBuilder = clone($queryBuilder);
        $count             = $countQueryBuilder
            ->select('COUNT(e)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $query = $queryBuilder
            ->getQuery()
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
            )
            ->setHint('knp_paginator.count', $count);

        return $query;
    }

    /**
     * Meant to be used in a basic index action.
     *
     * @return array An array containing the entities.
     */
    protected function doIndex()
    {
        $request       = $this->getRequest();
        $filterManager = $this->getFilterManager();

        if ($request->isMethod('POST')) {
            $filterManager->registerFilters($this->getEntityName(), $this->getGlobalFilters());

            return $this->redirect($this->generateUrl($this->getControllerIndex()));
        }

        // pagination
        $defaultSort = $this->getDefaultSort();
        // les configs knp_paginator.default_options.sort_field_name et sort_direction_name sont intégrées dans le service knp_paginator,
        // dans une propriété protected sans getter, donc on ne peut pas récupérer les valeurs
        if ($this->getRequest()->query->get('sort') == null && is_array($defaultSort)) {
            // Knp\Component\Pager\Event\Subscriber\Sortable\Doctrine\ORM\QuerySubscriber lit ses valeurs dans $_GET, donc, on les écrit là, sans passer par Request
            $_GET['sort'] = $defaultSort['sort'];
            $_GET['direction'] = (array_key_exists('direction', $defaultSort)) ? $defaultSort['direction'] : 'asc';
            //$paginatorParams[$paginatorSortName] = $defaultSort['sort'];
            //$paginatorParams[$paginatorDirectionName] = (array_key_exists('direction', $defaultSort)) ? $defaultSort['direction'] : 'asc';
        }

        $result = $this->getQuery();

        try {
            $items = $this->getPagination($result, $this->getElementsPerPage());
        } catch (\Exception $e) {
            $items = array();
            $filterManager->clearFilters($this->getEntityName());
            $this->getSession()->getFlashBag()->add('error', $this->get('translator')->trans('bigfoot_core.crud.index.error', array('%error%' => $e->getMessage())));
        }

        return $this->renderIndex($items, $result->getHint('knp_paginator.count'));
    }

    /**
     * Checks if the entity is valid
     *
     * @return boolean
     */
    protected function isEntityValid($form)
    {
        return true;
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
        $action      = $this->generateUrl(
            $this->getRouteNameForAction('new'),
            array('layout' => $request->get('layout', null))
        );

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid() and $this->isEntityValid($form)) {
                $this->prePersist($entity, 'new');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'new');

                if (!$request->isXmlHttpRequest()) {
                    $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId()));

                    $this->addSuccessFlash('bigfoot_core.flash.new.confirm');

                    return $this->redirect($action);
                } else {
                    return $this->handleSuccessResponse('new', $entity);
                }
            } else {
                /** @var Session $session */
                $session = $this->get('session');
                $session->set('bigfoot_core.crud.form.'.$this->getName().'.errors', $this->getFormErrorsAsArray($form));
            }

            if ($request->isXmlHttpRequest()) {
                return $this->renderAjax(
                    false,
                    'Error during process!',
                    $this->renderForm($form, $action, $entity)->getContent()
                );
            }

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('new')));
        }

        return $this->renderForm($form, $action, $entity, 'new');
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
        $entity = $this->getFormEntity($id);

        if (!$entity) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.edit.errors.not_found',
                    array('%entity%' => $this->getEntity() . ' (id: ' . $id . ')')
            )
            );
        }

        $form   = $this->createForm($this->getFormType(), $entity);
        $action = $this->generateUrl(
            $this->getRouteNameForAction('edit'),
            array('id' => $entity->getId(), 'layout' => $request->get('layout', null))
        );

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid() and $this->isEntityValid($form)) {
                $this->prePersist($entity, 'edit');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'edit');

                if (!$request->isXmlHttpRequest()) {
                    $this->addSuccessFlash('bigfoot_core.flash.edit.confirm');

                    return $this->redirect($action);
                } else {
                    return $this->handleSuccessResponse('edit', $entity);
                }
            } else {
                /** @var Session $session */
                $session = $this->get('session');
                $session->set('bigfoot_core.crud.form.'.$this->getName().'.errors', $this->getFormErrorsAsArray($form));
            }

            if ($request->isXmlHttpRequest()) {
                return $this->renderAjax(
                    false,
                    'Error during process!',
                    $this->renderForm($form, $action, $entity)->getContent()
                );
            }

            return $this->redirect(
                $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId()))
            );
        }

        return $this->renderForm($form, $action, $entity, 'edit');
    }

    /**
     * Helper deleting an entity through Doctrine.
     *
     * Redirects to the index action.
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doDelete(Request $request, $id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.delete.errors.not_found',
                    array('%entity%', $this->getEntity())
                )
            );
        }

        $this->removeAndFlush($entity);

        if (!$request->isXmlHttpRequest()) {
            $this->addSuccessFlash('bigfoot_core.flash.delete.confirm');

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('index')));
        } else {
            return $this->renderAjax(true, $this->getTranslator()->trans('bigfoot_core.delete.confirm'));
        }
    }

    /**
     * Helper deleting a file through file system.
     *
     * Redirects to the entity form.
     *
     * @param Request $request
     * @param         $id
     * @param         $property
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If $property does not exist on entity's
     *                                                                       class.
     * @throws \Exception If $property setter does not exists on class
     */
    protected function doDeleteFile(Request $request, $id, $property)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.delete.errors.not_found',
                    array('%entity%', $this->getEntity())
                )
            );
        }

        $reflClass = new \ReflectionClass(get_class($entity));
        if (!$reflClass->hasProperty($property)) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.delete_file.errors.not_found',
                    array('%property%' => $property, '%class' => get_class($entity))
                )
            );
        }

        $setFileFunction = 'set'.ucfirst($property);
        if (!method_exists($entity, $setFileFunction)) {
            throw new \Exception('The method '.$setFileFunction.' does not exist on '.get_class($entity).' class');
        }

        $fileManager = $this->getFileManager();
        $file        = $fileManager->getFileAbsolutePath($entity, $property);
        if ($file && file_exists($file)) {
            $fileManager->deleteFile($entity, $property);
            $entity->$setFileFunction(null);
            $this->persistAndFlush($entity);
        }

        if (!$request->isXmlHttpRequest()) {
            $this->addSuccessFlash('bigfoot_core.flash.delete_file.confirm');

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $id)));
        } else {
            return $this->renderAjax(true, $this->getTranslator()->trans('bigfoot_core.delete_file.confirm'));
        }
    }

    /**
     * Helper duplicate an entity through Doctrine.
     *
     * Redirects to the index action.
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If no entity with id $id is found.
     */
    protected function doDuplicate(Request $request, $id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.delete.errors.not_found',
                    array('%entity%', $this->getEntity())
                )
            );
        }

        // You need implemente __clone method in your entity to format object like you want
        $new = clone($entity);
        $this->persistAndFlush($new);

        $this->addSuccessFlash('bigfoot_core.flash.duplicate.confirm');

        return $this->redirect($this->generateUrl($this->getRouteNameForAction('index')));
    }

    /**
     * Render index
     *
     * @param array $items
     * @param int $count
     * @return Response
     */
    protected function renderIndex($items, $count)
    {
        $fields = array();
        foreach ($this->getFields() as $key => $field) {
            if (!is_array($field)) {
                $fields[$field] = array(
                    'label' => sprintf('bigfoot_core.crud.fields.%s.%s.label', $this->getName(), $field),
                );
            } else {
                if (!isset($field['label'])) {
                    $field['label'] = sprintf('bigfoot_core.crud.fields.%s.%s.label', $this->getName(), $key);
                }
                $fields[$key] = $field;
            }
        }
        
        $actions = $this->getActions();
        $actionsUrls = array();
        foreach ($items as $item) {
            foreach ($actions as $actionId => $action) {
                if (array_key_exists($actionId, $actionsUrls) === false) {
                    $actionsUrls[$actionId] = array();
                }
                $actionsUrls[$actionId][$item->getId()] = $this->getActionUrl($actionId, $action, $item);
            }
        }
        $isSearch = $this->getFilterManager()->hasSessionFilter(strtolower($this->getEntityName()));

        // pagination
        $defaultSort = $this->getDefaultSort();
        $paginatorParams = array();
        // les configs knp_paginator.default_options.sort_field_name et sort_direction_name sont intégrées dans le service knp_paginator,
        // dans une propriété protected sans getter, donc on ne peut pas récupérer les valeurs
        if ($this->getRequest()->query->get('sort') == null && is_array($defaultSort)) {
            $paginatorParams['sort'] = $defaultSort['sort'];
            $paginatorParams['direction'] = (array_key_exists('direction', $defaultSort)) ? $defaultSort['direction'] : 'asc';
        }

        return $this->render(
            $this->getIndexTemplate(),
            array(
                'list_items'       => $items,
                'list_title'       => $this->getListEntityLabel($count, $this->countEntities(), $isSearch),
                'list_fields'      => $fields,
                'actions'          => $actions,
                'actions_urls'     => $actionsUrls,
                'globalActions'    => $this->getGlobalActions(),
                'list_filters'     => $this->generateFiltersForm() ? $this->generateFiltersForm()->createView() : null,
                'paginator_params' => $paginatorParams
            )
        );
    }

    /**
     * Render form
     *
     * @param $form
     * @param string $action
     * @param stdclass $entity
     * @param string $visibility
     * @return Response
     */
    protected function renderForm($form, $action, $entity, $visibility = null)
    {
        return $this->render(
            $this->getFormTemplate(),
            array(
                'form'        => $form->createView(),
                'form_method' => 'POST',
                'form_title'  => $this->getFormEntityLabel($visibility),
                'form_action' => $action,
                'form_submit' => 'bigfoot_core.crud.submit',
                'form_cancel' => $this->getRouteNameForAction('index'),
                'entity'      => $entity,
                'layout'      => $this->getRequest()->query->get('layout') ?: '',
                'form_name'   => $this->getName(),
            )
        );
    }

    /**
     * @return string
     */
    protected function getNewUrl()
    {
        if (method_exists($this, 'newAction')) {
            return $this->generateUrl($this->getRouteNameForAction('new'));
        } else {
            return '';
        }
    }

    /**
     * Add sucess flash
     */
    protected function addSuccessFlash($message, array $additionnalActions = array())
    {
        $actions = array();

        $actions[] = array(
            'route' => $this->generateUrl($this->getRouteNameForAction('index')),
            'label' => 'bigfoot_core.flash.actions.back.label',
            'type'  => 'success',
        );

        if ($this->getNewUrl()) {
            $actions[] = array(
                'route' => $this->getNewUrl(),
                'label' => 'bigfoot_core.flash.actions.new.label',
                'type'  => 'success',
            );
        }

        if ($additionnalActions) {
            foreach ($additionnalActions as $action) {
                if (isset($action['route']) and isset($action['label'])) {
                    $actions[] = array(
                        'route' => $action['route'],
                        'label' => $action['label'],
                        'type'  => 'success',
                    );
                }
            }
        }

        $this->addFlash(
            'success',
            $this->renderView(
                $this->getThemeBundle().':admin:flash.html.twig',
                array(
                    'icon'        => 'ok',
                    'heading'     => 'bigfoot_core.flash.header.title.success',
                    'message'     => $message,
                    'actions'     => $actions,
                    'transParams' => array('%entity%' => $this->getEntityLabel()),
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

        return $this->renderAjax(true, $this->getTranslator()->trans('bigfoot_core.success.wait'), null, $action);
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

    /**
     * Post get query
     *
     * @param QueryBuilder $qb
     */
    protected function postQuery($qb)
    {

    }

    /**
     * @param $id
     *
     * @return object
     */
    protected function getFormEntity($id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);
        if (method_exists($entity, 'setTranslatableLocale')) {
            $entity->setTranslatableLocale($this->container->getParameter('locale'));
            $this->getEntityManager()->refresh($entity);
        }

        return $entity;
    }

    /**
     * @param string $env Environment, ex app, admin
     * @param string $route
     * @param array $parameters
     * @param boolean $envAutoSuffix Indicate if actual env suffix (ex _dev) will be added to $env, or not
     * @return string
     */
    protected function generateEnvUrl($env, $route, array $parameters = array(), $envAutoSuffix = true)
    {
        $requestContext = $this->get('router')->getContext();
        $oldBaseUrl = $requestContext->getBaseUrl();
        $baseUrl = '/' . $env;
        if ($envAutoSuffix) {
            $posSeparator = strpos($oldBaseUrl, '_');
            if ($posSeparator !== false) {
                $baseUrl .= substr($oldBaseUrl, $posSeparator, -4);
            }
        }
        $baseUrl .= '.php';
        $requestContext->setBaseUrl($baseUrl);
        $url = $this->generateUrl($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        $requestContext->setBaseUrl($oldBaseUrl);

        return $url;
    }

    /**
     * Get action url for an item
     *
     * @param string $actionId
     * @param array $action
     * @param stdclass $item
     * @return string
     */
    protected function getActionUrl($actionId, array $action, $item) {
        return null;
    }

    /**
     * Count entities
     *
     * @return int
     */
    protected function countEntities()
    {
        $queryBuilder = $this->getRepository($this->getEntityClass())->createQueryBuilder('a');
        $queryBuilder->select('COUNT(a)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array
     */
    protected function getDefaultSort()
    {
        return null;
    }
}
