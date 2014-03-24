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
        $menu = $this->factory->createItem(
            'home',
            array(
                'label' => 'Home',
            )
        );

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

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $settingsMenu = $menu->addChild(
                'settings',
                array(
                    'label'          => 'Settings',
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
        $this->eventDispatcher->dispatch(MenuEvent::TERMINATE, new GenericEvent($menu));

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