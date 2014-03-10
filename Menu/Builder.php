<?php

namespace Bigfoot\Bundle\CoreBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $menu = $this->factory->createItem('Home');

        $menu->setChildrenAttributes(
            array(
                'class' => 'nav nav-list',
            )
        );

        if ($this->security->isGranted('ROLE_ADMIN')) {
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
                    ),
                    'extras' => array(
                        'routes' => array(
                            'admin_content_template_choose',
                        )
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
        }

        $this->eventDispatcher->dispatch(MenuEvent::GENERATE_MAIN, new GenericEvent($menu));

        return $menu;
    }

    public function createTestMenu(Request $request)
    {
        $dbMenu = $this->entityManager->getRepository('BigfootNavigationBundle:Menu')->findOneByName('test');

        if (!$dbMenu) {
            throw new NotFoundHttpException("Menu not found");
        }

        $menu = $this->factory->createItem('test');

        $menu->setChildrenAttributes(
            array(
                'class' => 'nav nav-list',
            )
        );

        return $this->generateMenu($menu, $dbMenu);
    }

    public function generateMenu($menu, $dbMenu) {
        foreach ($dbMenu->getItems() as $item) {
            $route = array(
                'label'          => $item->getName(),
                'route'          => $item->getRoute(),
                'linkAttributes' => array(
                    'class' => 'dropdown-toggle',
                    'icon'  => 'wrench',
                ),
            );

            $parameters = $item->getParameters();

            if (count($parameters)) {
                foreach ($parameters as $parameter) {
                    $route['routeParameters'][$parameter->getName()] = $parameter->getValue();
                }
            }

            $menu->addChild(
                $item->getName(),
                $route
            );
        }

        return $menu;
    }
}