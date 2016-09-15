<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper type allowing custom display of form types in collections.
 *
 * Meant to be used on types used as embedded forms in collections : adds a "Delete" link if allow_delete is set and adds the necessary HTML structure to make the form appear inside a collapsable element.
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class CollectionItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder, $options) {
                $form = $event->getForm();
                $data = $event->getData();

                if (!($data && $data->getId()) && $form->has('translation')) {
                    $form->remove('translation');
                }
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'opened_default' => true
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['opened_default'] = $options['opened_default'];
    }
}
