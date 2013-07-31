<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Ordering Collection items
 *
 * @package Bigfoot\Bundle\Core
 */
class SortableEntityType extends AbstractType
{

    public function getParent()
    {
        return 'hidden';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'  => false,
            'attr'      => array(
                'class' => 'sortable-field'
            ),
            'data'      => 0
        ));
    }

    public function getName()
    {
        return 'sortable_entity';
    }
}
