<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Manager\TranslatableLabelManager;
use Doctrine\ORM\Query;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

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
                'width' => '200px'
            ),
            'name' => array(
                'width' => '300px'
            ),
            'value',
            'editedAt' => array(
                'formatters' => array(
                    'date'
                ),
                'width' => '120px'
            ),
        );
    }

    /**
     * @return array
     */
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
                'placeholder' => 'Identifier',
                'name'        => 'name',
                'type'        => 'search',
                'options' => array(
                    'properties' => array(
                        'name',
                    )
                )
            ),
            array(
                'placeholder' => 'Translation',
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
        return 'bigfoot_core.crud.controller.admin_translatable_label.entity.label_plural';
    }

    /**
     * @return string
     */
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
        return $this->doEdit($request, $id);
    }

    /**
     * @param $id
     * @return object
     */
    protected function getFormEntity($id)
    {
        $entity = parent::getFormEntity($id);
        if ($entity) {
            $entity->setTranslatableLocale($this->container->getParameter('locale'));
            $this->getEntityManager()->refresh($entity);
        }

        return $entity;
    }

    /**
     * Post flush entity
     *
     * @param object $entity entity
     * @param string $action
     */
    protected function postFlush($entity, $action)
    {
        /** @var TranslatableLabelManager $labelManager */
        $labelManager = $this->get('bigfoot_core.manager.translatable_label');
        $labelManager->clearTranslationCache();
    }
}
