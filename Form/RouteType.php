<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RouteType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class RouteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', array(
                'required' => true
            ))
            ->add('type', 'text', array(
                'required' => true
            ))
            ->add('objectClass', 'text', array(
                'required' => true
            ))
            ->add('foreignKey', 'text', array(
                'required' => true
            ))
            ->add('action', 'text', array(
                'required' => true
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\Route'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_bundle_corebundle_routetype';
    }
}
