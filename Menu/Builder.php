<?php

namespace Bigfoot\Bundle\CoreBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var EventDispatcher
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
     * @param SecurityContextInterface $security
     * @param EventDispatcher          $eventDispatcher
     * @param MenuManager              $menuManager
     */
    public function __construct(EntityManager $entityManager, SecurityContextInterface $security, EventDispatcher $eventDispatcher, MenuManager $menuManager)
    {
        $this->entityManager   = $entityManager;
        $this->security        = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->menuManager     = $menuManager;
    }

    /**
     * Create main menu
     *
     * @param  Request $request
     *
     * @return Menu
     */
    public function createMainMenu(Request $request)
    {
        $builder = $this
            ->menuManager
            ->createRoot(
                'home',
                array(
                    'label' => 'Home'
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
                    'linkAttributes' => array(
                        'class' => 'dropdown-toggle',
                        'icon'  => 'wrench',
                    ),
                ),
                array(
                    'children-attributes' => array(
                        'class' => 'submenu'
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
     * @param  Request $request
     *
     * @return Menu
     */
    public function createTestMenu(Request $request)
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
