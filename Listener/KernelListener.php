<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class KernelListener
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class KernelListener
{
    /** @var \Gedmo\Translatable\TranslatableListener */
    protected $translationListener;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param TranslatableListener $translationListener
     * @param string $defaultLocale
     */
    public function __construct(TranslatableListener $translationListener, $defaultLocale)
    {
        $this->translationListener = $translationListener;
        $this->defaultLocale       = $defaultLocale;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if ($locale = $request->getSession()->get('_locale', false)) {
            $request->setLocale($locale);
        } elseif ($locale = $request->getPreferredLanguage()) {
            $request->setLocale($locale);
        } else {
            $request->setLocale($this->defaultLocale);
        }
    }

    public function onLateKernelRequest(GetResponseEvent $event)
    {
        $this->translationListener->setTranslatableLocale($event->getRequest()->getLocale());
    }
}
