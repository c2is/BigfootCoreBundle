<?php

namespace Bigfoot\Bundle\CoreBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\ORM\EntityManager;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Bigfoot\Bundle\CoreBundle\Menu\MenuManager;

/**
 * Menu Builder
 */
class Builder
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TokenStorage
     */
    protected $security;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var MenuManager
     */
    protected $menuManager;

    /**
     * Constructor
     *
     * @param EntityManager            $entityManager
     * @param TokenStorage $security
     * @param EventDispatcherInterface          $eventDispatcher
     * @param MenuManager              $menuManager
     */
    public function __construct(EntityManager $entityManager, TokenStorage $security, EventDispatcherInterface $eventDispatcher, MenuManager $menuManager)
    {
        $this->entityManager   = $entityManager;
        $this->security        = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->menuManager     = $menuManager;
    }

    /**
     * Create main menu
     *
     * @return Menu
     */
    public function createMainMenu()
    {
        $builder = $this
            ->menuManager
            ->createRoot(
                'home',
                array(
                    'label' => 'Home',
                    'route' => 'admin_home',
                    'linkAttributes' => array(
                        'icon' => 'home',
                    )
                ),
                array(
                    'children-attributes' => array(
                        'class' => 'nav nav-list'
                    )
                )
            )
            ->addChild(
                'dashboard',
                array(
                    'label'          => 'Dashboard',
                    'route'          => 'admin_home',
                    'linkAttributes' => array(
                        'class' => 'dropdown-toggle',
                        'icon'  => 'dashboard',
                    ),
                )
            )
            ->addChild(
                'settings',
                array(
                    'label'          => 'Settings',
                    'url'            => '#',
                    'attributes' => array(
                        'class' => 'parent',
                    ),
                    'linkAttributes' => array(
                        'class' => 'dropdown-toggle',
                        'icon'  => 'wrench',
                    ),
                ),
                array(
                    'children-attributes' => array(
                        'class' => 'submenu parent'
                    )
                )
            );

        $this->eventDispatcher->dispatch(MenuEvent::GENERATE_MAIN, new GenericEvent($builder));
        $this->eventDispatcher->dispatch(MenuEvent::TERMINATE, new GenericEvent($builder));

        $menu = $builder->createMenu();

        $this->eventDispatcher->dispatch(MenuEvent::RENDER_MENU, new GenericEvent($menu));

        return $menu;
    }

    /**
     * Create test menu
     *
     * @return Menu
     */
    public function createTestMenu()
    {
        // $dbMenu = $this->entityManager->getRepository('BigfootNavigationBundle:Menu')->findOneByName('test');

        // if (!$dbMenu) {
        //     throw new NotFoundHttpException("Menu not found");
        // }

        // $menu = $this->factory->createItem('test');

        // $menu->setChildrenAttributes(
        //     array(
        //         'class' => 'nav nav-list',
        //     )
        // );

        // return $this->generateMenu($menu, $dbMenu);
    }

    /**
     * Generate menu
     *
     * @param  mixed $menu
     * @param  mixed $dbMenu
     *
     * @return mixed
     */
    public function generateMenu($menu, $dbMenu)
    {
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
