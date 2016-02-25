<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\CoreBundle\Form\DataTransformer\TagsToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extends the Symfony date type to use a localized date format automatically
 *
 * Class BigfootDatepickerType
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class BigfootDatepickerType extends AbstractType
{
    /** @var string */
    protected $format;

    /**
     * @param ContextService $context
     */
    public function __construct(ContextService $context)
    {
        $config = $context->get('language_back', true);
        $this->format = $config['parameters']['date_format'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => $this->format,
        ));
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'date';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'bigfoot_datepicker';
    }
}