<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Translation type used to automatically add translation on forms depending of translatable objects.
 *
 * @package Bigfoot\Bundle\SeoBundle\Form
 */
class TranslatedEntityType extends AbstractType
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var
     */
    protected $localeList;

    /**
     * @param ContainerInterface $container
     * @param $localeList
     */
    public function __construct(ContainerInterface $container, $localeList)
    {
        $this->container = $container;
        $this->localeList = $localeList;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translatedEntity', 'hidden' );

        $builder->addEventSubscriber(new TranslationSubscriber($this->localeList, $this->container->get('doctrine'), $this->container->get('annotation_reader'), $this->container->get('request')->getLocale(), $this->container->getParameter('locale')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_class' => null,
            'mapped' => false,
            'label' => false,
            'attr' => array(
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
