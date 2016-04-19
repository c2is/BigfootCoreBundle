<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Bigfoot\Bundle\CoreBundle\Entity\Route;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $arrayType = Route::getAllRouteType();

        $builder
            ->add('url', TextType::class, array(
                'required' => true
            ))
            ->add('type', ChoiceType::class, array(
                'choices'  => array_flip($arrayType),
                'required' => true
            ))
            ->add('objectClass', TextType::class, array(
                'required' => false
            ))
            ->add('variableName', TextType::class, array(
                'required' => false
            ))
            ->add('foreignKey', TextType::class, array(
                'required' => false
            ))
            ->add('action', TextType::class, array(
                'required' => true
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
