<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class KernelListener
 *
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class KernelListener
{
    /** @var \Gedmo\Translatable\TranslatableListener */
    protected $translationListener;

    /** @var Kernel */
    protected $kernel;

    /** @var string */
    protected $defaultBackLocale;

    /** @var \Bigfoot\Bundle\ContextBundle\Service\ContextService */
    protected $context;

    /**
     * @param TranslatableListener                                 $translationListener
     * @param Kernel                                               $kernel
     * @param \Bigfoot\Bundle\ContextBundle\Service\ContextService $context
     *
     * @internal param array $allowedLocales
     */
    public function __construct(TranslatableListener $translationListener, Kernel $kernel, ContextService $context)
    {
        $contexts = $context->getContexts();

        $this->translationListener = $translationListener;
        $this->kernel              = $kernel;
        $this->defaultBackLocale   = $contexts['language_back']['default_value'];
        $this->context             = $context;
        $this->allowedLocales      = array_keys($context->getValues('language_back'));
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() or !in_array(
                $this->kernel->getEnvironment(),
                array('admin', 'admin_dev')
            )
        ) {
            return;
        }

        $request = $event->getRequest();
        $locale  = $this->defaultBackLocale;

        if (($guessedLocale = $request->getSession()->get('_locale', false)) && in_array(
                $guessedLocale,
                $this->allowedLocales
            )
        ) {
            $locale = $guessedLocale;
        } elseif (($guessedLocale = $request->getPreferredLanguage()) && in_array(
                $guessedLocale,
                $this->allowedLocales
            )
        ) {
            $locale = $guessedLocale;
        } else {
            $request->setLocale($this->defaultBackLocale);
        }

        $request->setLocale($locale);
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onLateKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() or ! in_array(
                $this->kernel->getEnvironment(), array('admin', 'admin_dev')
            )
        ) {
            return;
        }

        $this->translationListener->setTranslatableLocale($this->context->getDefaultFrontLocale());
    }
}
