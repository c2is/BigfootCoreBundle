<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\CoreBundle\Form\DataTransformer\TagsToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DateTimePickerType
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class DateTimePickerType extends AbstractType
{
    /** @var string */
    protected $format;

    /**
     * @param ContextService $context
     */
    public function __construct(ContextService $context)
    {
        $config       = $context->get('language_back', true);
        $this->format = $config['parameters']['date_format'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $dateOptions = $builder->get('date')->getOptions();
        $timeOptions = $builder->get('time')->getOptions();
        $dateOptions['inDateTime']  = true;
        $timeOptions['inDateTime']  = true;
        $builder->remove('date')->add('date', BigfootDatepickerType::class, $dateOptions);
        $builder->remove('time')->add('time', TimePickerType::class, $timeOptions);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'date_widget'  => 'single_text',
                'date_format'  => $this->format,
                'time_widget'  => 'single_text',
                'with_seconds' => true,
            )
        );
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return DateTimeType::class;
    }
}
