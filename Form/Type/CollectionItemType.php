<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Helper type allowing custom display of form types in collections.
 *
 * Meant to be used on types used as embedded forms in collections : adds a "Delete" link if allow_delete is set and adds the necessary HTML structure to make the form appear inside a collapsable element.
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class CollectionItemType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_collection_item';
    }
}
