<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;
use Bigfoot\Bundle\CoreBundle\Theme\Theme;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class ControllerListener
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class ControllerListener
{
    /**
     * @var \Bigfoot\Bundle\CoreBundle\Theme\Theme
     */
    protected $theme;

    /**
     * @param Theme $theme
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AdminControllerInterface) {
            $this->theme['page_header']['title'] = $controller[0]->getControllerTitle();
        }
    }
}