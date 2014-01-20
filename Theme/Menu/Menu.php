<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Menu;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Represents a group of links to be used to display navigation elements in the BackOffice.
 */
class Menu
{
    /**
     * @var string Name of the menu. Used as a key in associative arrays.
     */
    protected $name;

    /**
     * @var array An array of \Bigfoot\Core\Theme\Menu\Item objects. The menu's links.
     */
    protected $items = array();

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param string $name Name of the menu.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string The name of the menu.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds an item to the menu.
     *
     * @param Item $item The item to be added.
     */
    public function addItem(Item $item)
    {
        $this->items[$item->getName()] = $item;
    }

    /**
     * @param $name
     * @param Item $child
     * @return $this
     */
    public function addOnItem($name, Item $child)
    {
        $item = $this->getItem($name);

        if (!$item) {
            $item = new Item(ucfirst($name));
            $this->addItem($item);
        }

        $item->addChild($child);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed Item|null
     */
    public function getItem($name)
    {
        return array_key_exists($name, $this->items) ? $this->items[$name] : null;
    }

    /**
     * @return array The menu items associated to the menu.
     */
    public function getItems()
    {
        try {
            $userConfig = $this->container->getParameter('bigfoot_user');

            $securityContext = $this->container->get('security.context');
            $items = array();

            // check for edit access for every item
            foreach($this->items as $item) {

                $roleForItem = array('ROLE_ADMIN');

                if(isset($userConfig['menu_security'][$item->getName()]))
                {
                    $roleForItem = array_merge(array('ROLE_ADMIN'), $userConfig['menu_security'][$item->getName()]);
                }

                if ($securityContext->isGranted($roleForItem)) {
                    $items[] = $item;
                }
            }

            return $items;
        }
        catch (InvalidArgumentException $e) {
            return $this->items;
        }
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
