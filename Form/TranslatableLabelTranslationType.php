<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelTranslation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TranslatableLabelTranslationType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class TranslatableLabelTranslationType extends AbstractTranslatableLabelType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var TranslatableLabelTranslation $translation */
                $translation = $event->getData();
                $form = $event->getForm();

                if (!$translation) {
                    return;
                }

                /** @var TranslatableLabel $label */
                $label = $form->getParent()->getParent()->getData();
                $labelManager = $this->labelManager;

                if ($label->isPlural()) {
                    $this->managePlural($label, $translation->getContent(), $form, $translation->getLocale());
                } else {
                    $form->add('content', $labelManager->getValueFieldType($label), array(
                        'label' => 'bigfoot_core.translatable_label.form.value.label',
                        'required' => false,
                        'attr' => array(
                            'data-locale' => $translation->getLocale(),
                        ),
                    ));
                }

                $form->add(
                    'emptyValue',
                    'checkbox',
                    array(
                        'label' => 'bigfoot_core.translatable_label.form.empty_value.label',
                        'required' => false
                    )
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $label = $event->getData();
                $form = $event->getForm();

                if (!$label) {
                    return;
                }

                // If there is no value, it means it's a plural form label
                if (!isset($label['content'])) {
                    $labelValue = $this->aggregatePluralValues($label, $form);
                    $label['content'] = $labelValue;
                }

                $form->add('content');

                $event->setData($label);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var TranslatableLabelTranslation $label */
                $label = $event->getData();
                $form = $event->getForm();

                if (!$label) {
                    return;
                }

                if ($label->isEmptyValue() === true ) {
                    $label->setContent('');
                }
            })
        ;
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var TranslatableLabelTranslation $translation */
        $translation = $form->getData();

        $view->vars['attr']['data-locale'] = $translation->getLocale();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelTranslation',
            'label' => false,
            'attr' => array(
                'class' => 'translatable-label-container',
            )
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_bundle_corebundle_translatable_label_translationtype';
    }
}
