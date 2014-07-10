<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

use Bigfoot\Bundle\CoreBundle\Event\SettingsEvent;

/**
 * Class SettingsType
 *
 * @package BigfootCoreBundle
 */
class SettingsType extends AbstractType
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->eventDispatcher->dispatch(SettingsEvent::GENERATE, new GenericEvent($builder));
        $this->eventDispatcher->dispatch(SettingsEvent::COMPLETE, new GenericEvent($builder));
    }

    /**
     * setDefaultOptions
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => null,
            )
        );
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_core_settings_type';
    }
}
