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
class CollectionItemType extends AbstractType
{
    public function getName()
    {
        return 'bigfoot_collection_item';
    }
}