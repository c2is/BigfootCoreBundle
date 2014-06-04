<?php

namespace Bigfoot\Bundle\CoreBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Knp\Component\Pager\Event\ItemsEvent;

class KnpSubscriber implements EventSubscriberInterface
{
    private $request;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return array(
            // 'knp_pager.items' => array('items', 1)
        );
    }

    public function items(ItemsEvent $event)
    {
    }
}
