<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @param RequestStack $requestStack
     * @param $localeList
     */
    public function __construct(TranslationSubscriber $translationSubscriber, RequestStack $requestStack, $localeList)
    {
        $this->translationSubscriber = $translationSubscriber;
        $this->request               = $requestStack->getCurrentRequest();
        $this->localeList            = $localeList;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translatedEntity', HiddenType::class);
        $builder->addEventSubscriber($this->translationSubscriber);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
