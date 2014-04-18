<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TranslatedEntityType
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class TranslatedEntityType extends AbstractType
{
    /** @var \Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber */
    protected $translationSubscriber;

    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var array */
    protected $localeList;

    /**
     * @param TranslationSubscriber $translationSubscriber
     * @param Request $request
     * @param $localeList
     */
    public function __construct(TranslationSubscriber $translationSubscriber, Request $request, $localeList)
    {
        $this->translationSubscriber = $translationSubscriber;
        $this->request               = $request;
        $this->localeList            = $localeList;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translatedEntity', 'hidden' );

        $translationSubscriber = $this->translationSubscriber;
        $translationSubscriber->setLocale($this->request->getLocale());

        $builder->addEventSubscriber($translationSubscriber);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_class' => null,
            'mapped'            => false,
            'label'             => false,
            'attr'              => array(
                'class' => 'translatable-fields'
            ),
        ));
    }

    /**
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options = array())
    {
        return $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable_entity';
    }
}
