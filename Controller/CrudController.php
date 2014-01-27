<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Query;

use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;
use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;
use Bigfoot\Bundle\UserBundle\Entity\BigfootUser;
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

    public function getEntityLabel()
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
     * Will be used as a label for the "add new" menu item.
     *
     * @return string
     */
    protected function getAddLabel()
    {
        return sprintf('Add %s', $this->getEntityName());
    }

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

        return array(
            'list_items'    => $this->getPagination($query, 10),
            'list_title'    => $this->getEntityLabelPlural(),
            'list_fields'   => $this->getFields(),
            'actions'       => $this->getActions(),
            'globalActions' => $this->getGlobalActions(),
            'breadcrumbs'   => array(
                array(
                    'label' => $this->getEntityLabelPlural(),
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                )
            ),
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
        $form   = $this->createForm($this->getFormType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            if ($entity instanceof BigfootUser) {
                $this->getUserManager()->updatePassword($entity);
            }

            $this->persistAndFlush($entity);

            $tabPreview            = $this->container->getParameter('preview');
            $renderPreview         = array();
            $itemToMenu            = array();

            foreach ($tabPreview as $preview) {
                if (isset($preview[$this->getEntity()]) && isset($preview[$this->getEntity()]['route'])) {
                    $previewParameters = $preview[$this->getEntity()];
                    $tabParameters     = array();
                    $tabMenuParameters = array();

                    if (isset($previewParameters['parameters']) && sizeof($previewParameters['parameters']) > 0) {
                        foreach ($previewParameters['parameters'] as $parameter) {
                            $accessor = PropertyAccess::createPropertyAccessor();
                            $tabParameters[key($parameter)] = $accessor->getValue($entity, $parameter[key($parameter)]);
                            $tabMenuParameters[] = $accessor->getValue($entity, $parameter[key($parameter)]);
                        }
                    }

                    $renderPreview = array(
                        'route' => $this->container->get('router')->generate($previewParameters['route'], $tabParameters, true),
                        'label' => 'Preview',
                        'type'  => 'success'
                    );
                    break;
                }
            }

            if (isset($previewParameters['route']) && isset($tabMenuParameters)) {
                $itemToMenu = array(
                    'route' => $this->container->get('router')->generate('admin_menu_item_new', array('preview' => true, 'route' => $previewParameters['route'], 'value' => serialize($tabMenuParameters)), true),
                    'label' => 'Add this page to menu',
                    'type'  => 'success'
                );
            }

            $this->addFlash(
                'success',
                $this->render(
                    'BigfootCoreBundle:includes:flash.html.twig',
                    array(
                        'icon'    => 'ok',
                        'heading' => 'Success!',
                        'message' => sprintf('The %s has been created.', $this->getEntityName()),
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
                            ),
                            $renderPreview,
                            $itemToMenu
                        )
                    )
                )
            );

            if ($this->has('security.acl.provider')) {
                $aclProvider    = $this->get('security.acl.provider');
                $objectIdentity = ObjectIdentity::fromDomainObject($entity);
                $acl            = $aclProvider->createAcl($objectIdentity);

                $user             = $this->getUser();
                $securityIdentity = UserSecurityIdentity::fromAccount($user);

                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            }

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId())));
        }

        return array(
            'form'         => $form->createView(),
            'form_title'   => sprintf('%s creation', $this->getEntityLabel()),
            'form_action'  => $this->generateUrl($this->getRouteNameForAction('create')),
            'form_submit'  => 'Create',
            'cancel_route' => $this->getRouteNameForAction('index'),
            'isAjax'       => $request->isXmlHttpRequest(),
            'breadcrumbs'  => array(
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('new')),
                    'label' => sprintf('%s creation', $this->getEntityLabel())
                ),
            ),
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
            'form'         => $form->createView(),
            'form_title'   => sprintf('%s creation', $this->getEntityLabel()),
            'form_action'  => $this->generateUrl($this->getRouteNameForAction('create')),
            'form_submit'  => 'Create',
            'cancel_route' => $this->getRouteNameForAction('index'),
            'isAjax'       => $this->getRequest()->isXmlHttpRequest(),
            'breadcrumbs'  => array(
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('new')),
                    'label' => sprintf('%s creation', $this->getEntityLabel())
                ),
            ),
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
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $editForm = $this->createForm($this->getFormType(), $entity);

        $parameters = array(
            'form'              => $editForm->createView(),
            'form_method'       => 'PUT',
            'form_action'       => $this->generateUrl($this->getRouteNameForAction('update'), array('id' => $entity->getId())),
            'form_cancel_route' => $this->getRouteNameForAction('index'),
            'form_title'        => sprintf('%s edit', $this->getEntityLabel()),
            'isAjax'            => $this->getRequest()->isXmlHttpRequest(),
            'breadcrumbs'       => array(
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId())),
                    'label' => sprintf('%s edit', $this->getEntityLabel())
                ),
            ),
        );

        return $parameters;
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
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $editForm   = $this->createForm($this->getFormType(), $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            if ($entity instanceof BigfootUser) {
                $this->getUserManager()->updatePassword($entity);
            }

            $this->persistAndFlush($entity);

            $tabPreview            = $this->container->getParameter('preview');
            $renderPreview         = array();
            $itemToMenu            = array();

            foreach ($tabPreview as $preview) {
                if (isset($preview[$this->getEntity()]) && isset($preview[$this->getEntity()]['route'])) {
                    $previewParameters = $preview[$this->getEntity()];
                    $tabParameters     = array();
                    $tabMenuParameters = array();

                    if (isset($previewParameters['parameters']) && sizeof($previewParameters['parameters']) > 0) {
                        foreach ($previewParameters['parameters'] as $parameter) {
                            $accessor = PropertyAccess::createPropertyAccessor();
                            $tabParameters[key($parameter)] = $accessor->getValue($entity, $parameter[key($parameter)]);
                            $tabMenuParameters[] = $accessor->getValue($entity, $parameter[key($parameter)]);
                        }
                    }

                    $renderPreview = array(
                        'route' => $this->container->get('router')->generate($previewParameters['route'], $tabParameters, true),
                        'label' => 'Preview',
                        'type'  => 'success'
                    );
                    break;
                }
            }

            if (isset($previewParameters['route']) && isset($tabMenuParameters)) {
                $itemToMenu = array(
                    'route' => $this->container->get('router')->generate('admin_menu_item_new', array('preview' => true, 'route' => $previewParameters['route'], 'value' => serialize($tabMenuParameters)), true),
                    'label' => 'Add this page to menu',
                    'type'  => 'success'
                );
            }

            $this->addFlash(
                'success',
                $this->render(
                    'BigfootCoreBundle:includes:flash.html.twig',
                    array(
                        'icon'    => 'ok',
                        'heading' => 'Success!',
                        'message' => sprintf('The %s has been updated.', $this->getEntityName()),
                        'actions' => array(
                            array(
                                'route' => $this->generateUrl($this->getRouteNameForAction('index')),
                                'label' => 'Back to the listing',
                                'type'  => 'success',
                            ),
                            $renderPreview,
                            $itemToMenu
                        )
                    )
                )
            );

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $id)));
        }

        return array(
            'form'               => $editForm->createView(),
            'form_method'        => 'PUT',
            'form_action'        => $this->generateUrl($this->getRouteNameForAction('update'), array('id' => $entity->getId())),
            'form_cancel_route'  => $this->getRouteNameForAction('index'),
            'form_title'         => sprintf('%s edit', $this->getEntityLabel()),
            'delete_form_action' => $this->generateUrl($this->getRouteNameForAction('delete'), array('id' => $entity->getId())),
            'isAjax'             => $request->isXmlHttpRequest(),
            'breadcrumbs'        => array(
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $entity->getId())),
                    'label' => sprintf('%s edit', $this->getEntityLabel())
                ),
            ),
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
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', $this->getEntity()));
        }

        $this->removeAndFlush($entity);

        $this->addFlash(
            'success',
            $this->render(
                'BigfootCoreBundle:includes:flash.html.twig',
                array(
                    'icon'    => 'ok',
                    'heading' => 'Success!',
                    'message' => sprintf('The %s has been deleted.', $this->getEntityName()),
                )
            )
        );

        return $this->redirect($this->generateUrl($this->getRouteNameForAction('index')));
    }
}
