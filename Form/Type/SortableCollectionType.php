<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Helper type allowing sorting of form types in collections.
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class SortableCollectionType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_sortable_collection';
    }
}
