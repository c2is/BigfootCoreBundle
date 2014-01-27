<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Listener
 */
class MenuListener implements EventSubscriberInterface
{
    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MenuEvent::GENERATE_MAIN => 'onGenerateMain',
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $menu          = $event->getSubject();
        $structureMenu = $menu->getChild('structure');

        $tagMenu = $structureMenu->addChild(
            'tag_menu',
            array(
                'label'          => 'Tag',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'tag',
                )
            )
        );

        $tagMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $tagMenu->addChild(
            'category',
            array(
                'label'  => 'Category',
                'route'  => 'admin_tag_category',
                'extras' => array(
                    'routes' => array(
                        'admin_tag_category_new',
                        'admin_tag_category_edit'
                    )
                ),
                'linkAttributes' => array(
                    'icon' => 'double-angle-right',
                )
            )
        );

        $tagMenu->addChild(
            'tag',
            array(
                'label'  => 'Tag',
                'route'  => 'admin_tag',
                'extras' => array(
                    'routes' => array(
                        'admin_tag_new',
                        'admin_tag_edit'
                    )
                ),
                'linkAttributes' => array(
                    'icon' => 'double-angle-right',
                )
            )
        );
    }
}
