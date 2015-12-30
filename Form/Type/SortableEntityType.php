<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper type to be used on an entity attribute used for sorting.
 * Adds a class used in javascript to call the jQuery sortable library and allow drag&drop sorting.
 *
 * @package Bigfoot\Bundle\Core
 */
class SortableEntityType extends AbstractType
{
    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'required'  => false,
            'attr'      => array(
                'class' => 'sortable-field'
            ),
            'data'      => 0
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sortable_entity';
    }
}
