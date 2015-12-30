<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelTranslation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Interval;

/**
 * Class TranslatableLabelType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class TranslatableLabelType extends AbstractTranslatableLabelType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var TranslatableLabel $label */
                    $label = $event->getData();
                    $form = $event->getForm();

                    if (!$label) {
                        return;
                    }

                    $labelManager    = $this->labelManager;
                    $existingLocales = array();
                    $locales         = array_keys($this->context->getValues('language'));
                    $locales         = array_combine($locales, $locales);

                    unset($locales[$this->defaultLocale]);

                    /** @var TranslatableLabel $translation */
                    foreach ($label->getTranslations() as $translation) {
                        $existingLocales[] = $translation->getLocale();
                    }

                    $missingLocales = array_diff($locales, $existingLocales);

                    foreach ($missingLocales as $locale) {
                        $label->addTranslation(new TranslatableLabelTranslation($locale, 'value', ''));
                    }

                    if ($label->isPlural()) {
                        $this->managePlural($label, $label->getValue(), $form, $this->defaultLocale);
                    } else {
                        $form->add(
                            'value',
                            $labelManager->getValueFieldType($label),
                            array(
                                'label' => 'bigfoot_core.translatable_label.form.value.label',
                                'required' => false,
                                'attr' => array(
                                    'data-locale' => $this->defaultLocale,
                                )
                            )
                        );
                    }

                    $form->add(
                        'translations',
                        'collection',
                        array(
                            'type' => 'bigfoot_bundle_corebundle_translatable_label_translationtype',
                            'label' => false,
                        )
                    );

                }
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $label = $event->getData();
                    $form = $event->getForm();

                    if (!$label) {
                        return;
                    }

                    // If there is no value, it means it's a plural form label
                    if (!isset($label['value'])) {
                        $labelValue = $this->aggregatePluralValues($label, $form);
                        $label['value'] = $labelValue;
                    }

                    $form->add('value');

                    $event->setData($label);
                }
            );
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['locale'] = $this->defaultLocale;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel',
                'label' => false,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_bundle_corebundle_translatable_labeltype';
    }
}
