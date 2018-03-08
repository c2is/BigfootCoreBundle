<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Manager\FilterManager;
use Doctrine\ORM\Query;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    const POST_EDIT = 'bigfoot.post_edit';
    const POST_NEW  = 'bigfoot.post_new';

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
            ['%entity%' => $this->getEntityLabelPlural()]
        );
    }

    /**
     * Get filters
     *
     * @return array
     */
    protected function getFilters()
    {
        return [];
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

        $datas = [
            'referer' => $this->getEntity(),
            'fields'  => $filters,
        ];

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

            return $this->getTranslator()->trans($key, ['%entity%' => $this->getEntityLabel()]);
        }

        return $this->getTranslator()->trans(
            'bigfoot_core.crud.edit.title',
            ['%entity%' => $this->getEntityLabel()]
        );
    }

    /**
     * @param int  $countResults
     * @param int  $count
     * @param bool $isSearch
     *
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
        return $this->getEntityTypeClass();
    }

    /**
     * @return bool
     */
    protected function getFormTypes()
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return $this->getDoctrine()->getManager()->getClassMetadata($this->getEntity())->getName();
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

        return ['bundle' => $bundleName, 'entity' => $entityName];
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
            ['%entity%' => $this->getEntityName()]
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
        $actions = [];

        if (method_exists($this, 'editAction')) {
            $actions['edit'] = [
                'label' => 'bigfoot_core.crud.actions.edit.label',
                'route' => $this->getRouteNameForAction('edit'),
                'icon'  => 'edit',
                'color' => 'green',
            ];
        }

        if (method_exists($this, 'duplicateAction')) {
            $actions['duplicate'] = [
                'label' => 'bigfoot_core.crud.actions.duplicate.label',
                'route' => $this->getRouteNameForAction('duplicate'),
                'icon'  => 'copy',
                'color' => 'green',
            ];
        }

        if (method_exists($this, 'deleteAction')) {
            $actions['delete'] = [
                'label'      => 'bigfoot_core.crud.actions.delete.label',
                'route'      => $this->getRouteNameForAction('delete'),
                'icon'       => 'trash',
                'color'      => 'red',
                'class'      => 'confirm-action',
                'attributes' => [
                    'data-confirm-message' => $this->getTranslator()->trans(
                        'bigfoot_core.crud.actions.delete.confirm',
                        ['%entity%' => $this->getEntityLabel()]
                    ),
                ],
            ];
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
        $globalActions = [];

        if ($this->generateExportCsvLink()) {
            $csvArray             = $this->generateExportCsvLink();
            $globalActions['csv'] = [
                'label'      => 'bigfoot_core.crud.actions.csv.label',
                'route'      => $csvArray['route'],
                'parameters' => $csvArray['parameters'],
                'icon'       => 'icon-table',
            ];
        }

        if (method_exists($this, 'newAction')) {
            $globalActions['new'] = [
                'label'      => 'bigfoot_core.crud.actions.new.label',
                'route'      => $this->getRouteNameForAction('new'),
                'parameters' => [],
                'icon'       => 'icon-plus-sign',
            ];
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
        $entityName = $this->getEntityName();

        $queryBuilder = $this
            ->getContextRepository()
            ->createContextQueryBuilder($this->getEntityClass());

        foreach ($this->getFields() as $key => $field) {
            if (is_array($field) && isset($field['join'])) {
                $queryBuilder
                    ->leftJoin('e.'.$key, $field['join'])
                    ->addSelect($field['join']);
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
     * @param Request $request
     *
     * @return array An array containing the entities.
     */
    protected function doIndex(Request $request)
    {
        $filterManager = $this->getFilterManager();

        if ($request->isMethod('POST')) {
            $filterManager->registerFilters($this->getEntityName(), $this->getGlobalFilters());

            return $this->redirect($this->generateUrl($this->getControllerIndex()));
        }

        // pagination
        $defaultSort = $this->getDefaultSort();
        // les configs knp_paginator.default_options.sort_field_name et sort_direction_name sont intégrées dans le service knp_paginator,
        // dans une propriété protected sans getter, donc on ne peut pas récupérer les valeurs
        if ($request->query->get('sort') == null && is_array($defaultSort)) {
            // Knp\Component\Pager\Event\Subscriber\Sortable\Doctrine\ORM\QuerySubscriber lit ses valeurs dans $_GET, donc, on les écrit là, sans passer par Request
            $_GET['sort']      = $defaultSort['sort'];
            $_GET['direction'] = (array_key_exists('direction', $defaultSort)) ? $defaultSort['direction'] : 'asc';
            //$paginatorParams[$paginatorSortName] = $defaultSort['sort'];
            //$paginatorParams[$paginatorDirectionName] = (array_key_exists('direction', $defaultSort)) ? $defaultSort['direction'] : 'asc';
        }

        $result = $this->getQuery();

        try {
            $items = $this->getPagination($result, $this->getElementsPerPage(), $request);
        } catch (\Exception $e) {
            $items = [];
            $filterManager->clearFilters($this->getEntityName());
            $this->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('bigfoot_core.crud.index.error', ['%error%' => $e->getMessage()])
            );
        }

        return $this->renderIndex($items, $result->getHint('knp_paginator.count'), $request);
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
            ['layout' => $request->get('layout', null)]
        );

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid() and $this->isEntityValid($form)) {
                $this->prePersist($entity, 'new');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'new');

                $this->get('event_dispatcher')->dispatch(self::POST_NEW, new GenericEvent($entity));

                if (!$request->isXmlHttpRequest()) {
                    $action = $this->generateUrl($this->getRouteNameForAction('edit'), ['id' => $entity->getId()]);

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
                    $this->renderForm($request, $form, $action, $entity)->getContent()
                );
            }

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('new')));
        }

        return $this->renderForm($request, $form, $action, $entity, 'new');
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
    protected function doEdit(Request $request, $id, $index = null)
    {
        $entity = $this->getFormEntity($id);

        if (!$entity) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.edit.errors.not_found',
                    ['%entity%' => $this->getEntity().' (id: '.$id.')']
                )
            );
        }

        // Handle form by tab
        if ($this->getFormTypes() && $index != null) {
            $formTypes = $this->getFormTypes();
            $formType  = (isset($formTypes[$index]['form'])) ? $formTypes[$index]['form'] : $this->getFormType();
        } else {
            $formType = $this->getFormType();
        }

        $form             = $this->createForm($formType, $entity);
        $parameterActions = [
            'id'     => $entity->getId(),
            'layout' => $request->get('layout', null),
        ];

        if ($index != null) {
            $parameterActions['index'] = $index;
        }

        $action = $this->generateUrl(
            $this->getRouteNameForAction('edit'),
            $parameterActions
        );

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid() and $this->isEntityValid($form)) {
                $this->prePersist($entity, 'edit');

                $this->persistAndFlush($entity);

                $this->postFlush($entity, 'edit');

                $this->get('event_dispatcher')->dispatch(self::POST_EDIT, new GenericEvent($entity));

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
                    $this->renderForm($request, $form, $action, $entity)->getContent()
                );
            }

            return $this->redirect(
                $this->generateUrl($this->getRouteNameForAction('edit'), ['id' => $entity->getId()])
            );
        }

        return $this->renderForm($request, $form, $action, $entity, 'edit');
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
                    ['%entity%', $this->getEntity()]
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
                    ['%entity%', $this->getEntity()]
                )
            );
        }

        $reflClass = new \ReflectionClass(get_class($entity));
        if (!$reflClass->hasProperty($property)) {
            throw new NotFoundHttpException(
                $this->getTranslator()->trans(
                    'bigfoot_core.crud.delete_file.errors.not_found',
                    ['%property%' => $property, '%class' => get_class($entity)]
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

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('edit'), ['id' => $id]));
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
                    ['%entity%', $this->getEntity()]
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
     * @param array   $items
     * @param int     $count
     * @param Request $request
     *
     * @return Response
     */
    protected function renderIndex($items, $count, Request $request)
    {
        $fields = [];
        foreach ($this->getFields() as $key => $field) {
            if (!is_array($field)) {
                $fields[$field] = [
                    'label' => sprintf('bigfoot_core.crud.fields.%s.%s.label', $this->getName(), $field),
                ];
            } else {
                if (!isset($field['label'])) {
                    $field['label'] = sprintf('bigfoot_core.crud.fields.%s.%s.label', $this->getName(), $key);
                }
                $fields[$key] = $field;
            }
        }

        $actions     = $this->getActions();
        $actionsUrls = [];
        foreach ($items as $item) {
            foreach ($actions as $actionId => $action) {
                if (array_key_exists($actionId, $actionsUrls) === false) {
                    $actionsUrls[$actionId] = [];
                }
                $actionsUrls[$actionId][$item->getId()] = $this->getActionUrl($actionId, $action, $item);
            }
        }

        $isSearch = $this->getFilterManager()->hasSessionFilter(strtolower($this->getEntityName()));

        // pagination
        $defaultSort     = $this->getDefaultSort();
        $paginatorParams = [];
        // les configs knp_paginator.default_options.sort_field_name et sort_direction_name sont intégrées dans le service knp_paginator,
        // dans une propriété protected sans getter, donc on ne peut pas récupérer les valeurs
        if ($request->query->get('sort') == null && is_array($defaultSort)) {
            $paginatorParams['sort']      = $defaultSort['sort'];
            $paginatorParams['direction'] = (array_key_exists(
                'direction',
                $defaultSort
            )) ? $defaultSort['direction'] : 'asc';
        }

        return $this->render(
            $this->getIndexTemplate(),
            [
                'list_items'       => $items,
                'list_title'       => $this->getListEntityLabel($count, $this->countEntities(), $isSearch),
                'list_fields'      => $fields,
                'actions'          => $actions,
                'actions_urls'     => $actionsUrls,
                'globalActions'    => $this->getGlobalActions(),
                'list_filters'     => $this->generateFiltersForm() ? $this->generateFiltersForm()->createView() : null,
                'paginator_params' => $paginatorParams,
                'formTypes'        => $this->getFormTypes(),
            ]
        );
    }

    /**
     * Render form
     *
     * @param Request  $request
     * @param          $form
     * @param string   $action
     * @param stdclass $entity
     * @param string   $visibility
     *
     * @return Response
     */
    protected function renderForm(Request $request, $form, $action, $entity, $visibility = null)
    {
        return $this->render(
            $this->getFormTemplate(),
            [
                'form'        => $form->createView(),
                'form_method' => 'POST',
                'form_title'  => $this->getFormEntityLabel($visibility),
                'form_action' => $action,
                'form_submit' => 'bigfoot_core.crud.submit',
                'form_cancel' => $this->getRouteNameForAction('index'),
                'entity'      => $entity,
                'layout'      => $request->query->get('layout') ?: '',
                'form_name'   => $this->getName(),
                'formTypes'   => $this->getFormTypes(),
                'routeName'   => $this->getRouteNameForAction('edit'),
            ]
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
    protected function addSuccessFlash($message, array $additionnalActions = [])
    {
        $actions = [];

        $actions[] = [
            'route' => $this->generateUrl($this->getRouteNameForAction('index')),
            'label' => 'bigfoot_core.flash.actions.back.label',
            'type'  => 'success',
        ];

        if ($this->getNewUrl()) {
            $actions[] = [
                'route' => $this->getNewUrl(),
                'label' => 'bigfoot_core.flash.actions.new.label',
                'type'  => 'success',
            ];
        }

        if ($additionnalActions) {
            foreach ($additionnalActions as $action) {
                if (isset($action['route']) and isset($action['label'])) {
                    $actions[] = [
                        'route' => $action['route'],
                        'label' => $action['label'],
                        'type'  => 'success',
                    ];
                }
            }
        }

        $this->addFlash(
            'success',
            $this->renderView(
                $this->getThemeBundle().':admin:flash.html.twig',
                [
                    'icon'        => 'ok',
                    'heading'     => 'bigfoot_core.flash.header.title.success',
                    'message'     => $message,
                    'actions'     => $actions,
                    'transParams' => ['%entity%' => $this->getEntityLabel()],
                ]
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
     * @param string  $env           Environment, ex app, admin
     * @param string  $route
     * @param array   $parameters
     * @param boolean $envAutoSuffix Indicate if actual env suffix (ex _dev) will be added to $env, or not
     *
     * @return string
     */
    protected function generateEnvUrl($env, $route, array $parameters = [], $envAutoSuffix = true)
    {
        $requestContext = $this->get('router')->getContext();
        $oldBaseUrl     = $requestContext->getBaseUrl();
        $baseUrl        = '/'.$env;
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
     * @param string   $actionId
     * @param array    $action
     * @param stdclass $item
     *
     * @return string
     */
    protected function getActionUrl($actionId, array $action, $item)
    {
        return null;
    }

    /**
     * Count entities
     *
     * @return int
     */
    protected function countEntities()
    {
        $queryBuilder = $this->getRepository($this->getEntity())->createQueryBuilder('a');
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

    /**
     * @return string
     */
    protected function getIcon()
    {
        return null;
    }

    protected function getCsvFields()
    {
        return null;
    }

    private function generateExportCsvLink()
    {
        if ($this->getCsvFields()) {
            return [
                'route'      => 'admin_csv_generate',
                'parameters' => [
                    'entity' => base64_encode($this->getEntity()),
                    'fields' => str_replace('/', '+', base64_encode(serialize($this->getCsvFields()))),
                ],
            ];
        }

        return null;
    }
}
