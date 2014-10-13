<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * TranslatableLabel controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/translatable_label")
 */
class TranslatableLabelController extends CrudController
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'admin_translatable_label';
    }

    /**
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootCoreBundle:TranslatableLabel';
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        return array(
            'category' => array(
                'formatters' => array(
                    'trans'
                ),
            ),
            'name',
            'value',
            'editedAt' => array(
                'formatters' => array(
                    'date'
                ),
            ),
        );
    }

    protected function getFilters()
    {
        return array(
            array(
                'placeholder' => 'Category',
                'name'        => 'category',
                'type'        => 'repositoryMethod',
                'options' => array(
                    'method'        => 'addCategoryFilter',
                    'choicesMethod' => 'getCategories'
                )
            ),
            array(
                'placeholder' => 'Recherche',
                'name'        => 'search',
                'type'        => 'search',
                'options' => array(
                    'properties' => array(
                        'value',
                    )
                )
            ),
        );
    }

    /**
     * @return string
     */
    protected function getEntityLabelPlural()
    {
        return 'bigfoot_core.controller.admin_translatable_label.entity.label_plural';
    }

    protected function getFormType()
    {
        return 'bigfoot_bundle_corebundle_translatable_labeltype';
    }

    /**
     * Lists all TranslatableLabel entities.
     *
     * @Route("/", name="admin_translatable_label")
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Displays a form to edit an existing TranslatableLabel entity.
     *
     * @Route("/{id}/edit", name="admin_translatable_label_edit")
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->getRepository($this->getEntity())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException($this->getTranslator()->trans('bigfoot_core.crud.edit.errors.not_found', array('%entity%', $this->getEntity())));
        }

        $form   = $this->createForm($this->getFormType(), $entity);
        $action = $this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $id, 'layout' => $request->get('layout', null)));

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
                return $this->renderAjax(false, 'Error during process!', $this->renderForm($form, $action, $entity)->getContent());
            }

            return $this->redirect($this->generateUrl($this->getRouteNameForAction('edit'), array('id' => $id)));
        }

        return $this->renderForm($form, $action, $entity, 'edit');
    }
}
