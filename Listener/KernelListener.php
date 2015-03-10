<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class KernelListener
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class KernelListener
{
    /** @var \Gedmo\Translatable\TranslatableListener */
    protected $translationListener;

    /** @var Kernel */
    protected $kernel;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param TranslatableListener $translationListener
     * @param Kernel $kernel
     * @param string $defaultLocale
     * @param array $allowedLocales
     */
    public function __construct(TranslatableListener $translationListener, Kernel $kernel, $defaultLocale, $allowedLocales)
    {
        $this->translationListener = $translationListener;
        $this->kernel = $kernel;
        $this->defaultLocale = $defaultLocale;
        $this->allowedLocales = array_keys($allowedLocales);
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() or !in_array($this->kernel->getEnvironment(), array('admin', 'admin_dev'))) {
            return;
        }

        $request = $event->getRequest();
        $locale = $this->defaultLocale;

        if (($guessedLocale = $request->getSession()->get('_locale', false)) && in_array($guessedLocale, $this->allowedLocales)) {
            $locale = $guessedLocale;
        } elseif (($guessedLocale = $request->getPreferredLanguage()) && in_array($guessedLocale, $this->allowedLocales)) {
            $locale = $guessedLocale;
        } else {
            $request->setLocale($this->defaultLocale);
        }

        $request->setLocale($locale);
        $this->translationListener->setTranslatableLocale($locale);
    }
}
