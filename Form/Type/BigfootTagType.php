<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\DataTransformer\TagsToStringTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper type meant to facilitate the use of tags in entities.
 * Adds a ModelTransformer and the relevant HTML class to display a select2 field for tags.
 *
 * To be used for entities having a ManyToMany association with \Bigfoot\Bundle\CoreBundle\Entity\Tag.
 *
 * See http://ivaynberg.github.io/select2/#tags for more information on the select2 tag field.
 *
 * Class BigfootTagType
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class BigfootTagType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new TagsToStringTransformer($this->entityManager);
        $builder->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr'  => array(
                'class' => 'bigfoot_tags_field'
            )
        ));
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'bigfoot_tag';
    }
}