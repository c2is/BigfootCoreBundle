<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TranslatableLabelType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class TranslatableLabelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var TranslatableLabel $data */
                $data = $event->getData();
                $form = $event->getForm();

                if (!$data) return null;

                $valueType = $data->isMultiline() ? 'textarea' : 'text';

                $form
                    ->add('name')
                    ->add('value', $valueType, array(
                        'required' => false,
                    ))
                    ->add('translation', 'translatable_entity')
                ;

                if (!$data) return null;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                /** @var TranslatableLabel $data */
                $data = $event->getData();
                $form = $event->getForm();

                if (!$data) return null;
            })
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_bundle_corebundle_translatable_labeltype';
    }
}
