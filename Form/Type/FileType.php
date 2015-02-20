<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Helper type allowing custom display of form types in collections.
 *
 * Meant to be used on types used as embedded forms in collections : adds a "Delete" link if allow_delete is set and adds the necessary HTML structure to make the form appear inside a collapsable element.
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class FileType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        var_dump($options);
        var_dump($builder->getForm());die;
        if (!isset($options['filePathProperty']) || !$options['filePathProperty']) {
            throw new \Exception('BigfootFileType needs the options filePathProperty to be defined');
        }
        $builder
            ->setAttribute('filePathProperty', $options['filePathProperty'])
            ->setAttribute('deleteRoute', $options['deleteRoute'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['filePathProperty'] = $options['filePathProperty'];
        $view->vars['deleteRoute'] = $options['deleteRoute'];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'filePathProperty' => '',
            'deleteRoute' => '',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_file';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'file';
    }
}
