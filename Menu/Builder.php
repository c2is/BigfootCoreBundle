<?php

namespace Bigfoot\Bundle\CoreBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Builder
 */
class Builder
{
    protected $factory;
    protected $entityManager;
    protected $security;
    protected $eventDispatcher;

    /**
     * Construct Builder
     */
    public function __construct(FactoryInterface $factory, EntityManager $entityManager, SecurityContextInterface $security, EventDispatcher $eventDispatcher)
    {
        $this->factory         = $factory;
        $this->entityManager   = $entityManager;
        $this->security        = $security;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttributes(
            array(
                'class' => 'nav nav-list',
            )
        );

        $menu->addChild(
            'dashboard',
            array(
                'label'          => 'Dashboard',
                'route'          => 'admin_home',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'dashboard',
                ),
            )
        );

        $contentMenu = $menu->addChild(
            'content',
            array(
                'label'          => 'Content',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'list-alt',
                )
            )
        );

        $contentMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $mediaMenu = $menu->addChild(
            'media',
            array(
                'label'          => 'Media',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'picture',
                )
            )
        );

        $mediaMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $structureMenu = $menu->addChild(
            'structure',
            array(
                'label'          => 'Structure',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'building',
                )
            )
        );

        $structureMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $seoMenu = $menu->addChild(
            'seo',
            array(
                'label'          => 'Seo',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'rocket',
                )
            )
        );

        $seoMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $fluxMenu = $menu->addChild(
            'flux',
            array(
                'label'          => 'Flux',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'refresh',
                )
            )
        );

        $fluxMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $userMenu = $menu->addChild(
            'user',
            array(
                'label'          => 'Users',
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'group',
                )
            )
        );

        $userMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $settingsMenu = $menu->addChild(
            'Settings',
            array(
                'url'            => '#',
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'wrench',
                ),
            )
        );

        $settingsMenu->setChildrenAttributes(
            array(
                'class' => 'submenu',
            )
        );

        $this->eventDispatcher->dispatch(MenuEvent::GENERATE_MAIN, new GenericEvent($menu));

        return $menu;
    }
}