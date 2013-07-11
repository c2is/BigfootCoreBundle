<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Menu;

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

    protected $container;

    /**
     * Constructor.
     *
     * @param $name Name of the menu.
     */
    public function __construct($container, $name)
    {
        $this->container    = $container;
        $this->name         = $name;
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

    public function addOnItem($name, Item $child)
    {
        $item = $this->getItem($name);

        if (!$item) {
            $item = new Item($name);
            $this->addItem($item);
        }

        $item->addChild($child);

        return $this;
    }

    public function getItem($name)
    {
        return array_key_exists($name, $this->items) ? $this->items[$name] : null;
    }

    /**
     * @return array The menu items associated to the menu.
     */
    public function getItems()
    {
        return $this->items;
    }
}
