<?php

namespace Bigfoot\Bundle\CoreBundle\Subscriber;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Doctrine\ORM\EntityManager;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Subscriber
 */
class MenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorage
     */
    private $security;

    /**
     * @param TokenStorage $security
     */
    public function __construct(TokenStorage $security)
    {
        $this->security = $security;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MenuEvent::GENERATE_MAIN => array('onGenerateMain', 6)
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $builder = $event->getSubject();

        if (!$builder->childExists('structure')) {
            $builder
                ->addChild(
                    'structure',
                    array(
                        'label'      => 'Structure',
                        'url'        => '#',
                        'attributes' => array(
                            'class' => 'parent',
                        ),
                        'linkAttributes' => array(
                            'class' => 'dropdown-toggle fa fa-building',
                        )
                    ),
                    array(
                        'children-attributes' => array(
                            'class' => 'submenu'
                        )
                    )
                );
        }

        if (!$builder->childExists('content')) {
            $builder
                ->addChild(
                    'content',
                    array(
                        'label'      => 'Content',
                        'url'        => '#',
                        'attributes' => array(
                            'class' => 'parent',
                        ),
                        'linkAttributes' => array(
                            'class' => 'dropdown-toggle fa fa-building',
                        )
                    ),
                    array(
                        'children-attributes' => array(
                            'class' => 'submenu'
                        )
                    )
                );
        }

        $builder
            ->addChildFor(
                'structure',
                'structure_tag',
                array(
                    'label'          => 'Tag',
                    'url'            => '#',
                    'linkAttributes' => array(
                        'class' => 'dropdown-toggle',
                        'icon'  => 'tag',
                    )
                ),
                array(
                    'children-attributes' => array(
                        'class' => 'submenu'
                    )
                )
            )
            ->addChildFor(
                'structure_tag',
                'structure_tag_category',
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
            )
            ->addChildFor(
                'structure_tag',
                'structure_tag_tag',
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
            )
            ->addChildFor(
                'settings',
                'settings_global',
                array(
                    'label'  => 'bigfoot_core.menu.label.settings',
                    'route'  => 'admin_settings_global',
                    'linkAttributes' => array(
                        'icon'  => 'wrench',
                    )
                )
            )
            ->addChildFor(
                'settings',
                'settings_route',
                array(
                    'label'  => 'Route',
                    'route'  => 'bigfoot_route',
                    'linkAttributes' => array(
                        'icon'  => 'wrench',
                    )
                )
            )
            ->addChildFor(
                'content',
                'content_translations',
                array(
                    'label'  => 'bigfoot_core.menu.label.translations',
                    'route'  => 'admin_translatable_label',
                    'extras' => array(
                        'routes' => array(
                            'admin_translatable_label_edit',
                        )
                    ),
                    'linkAttributes' => array(
                        'icon'  => 'flag',
                    )
                )
            );
    }
}
