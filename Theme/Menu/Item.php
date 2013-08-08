<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Menu;

/**
 * Represents a link, to be used to display navigation elements in the BackOffice.
 */
class Item
{
    /**
     * @var string Name of the item. Used as a key in associative arrays.
     */
    protected $name;

    /**
     * @var string Label of the item. Will be used for display.
     */
    protected $label;

    /**
     * @var string The icon to be displayed with this link. Must correspond to a twitter bootstrap icon-xxxx.
     */
    protected $icon;

    /**
     * @var string Route the item should link to.
     */
    protected $route;

    /**
     * @var Item the parent item.
     */
    protected $parent;

    /**
     * @var array the collection of child Item.
     */
    protected $childs = array();

    /**
     * @var array List of parameters to be passed to the URL generator when generating the link.
     */
    protected $parameters = array();

    /**
     * @var array List of CSS attributes to output when displaying the item.
     */
    protected $attributes = array();

    /**
     * Constructor.
     *
     * @param $name string The name of the menu item.
     * @param $route string The route the item should link to.
     * @param array $parameters List of parameters to be passed to the URL generator when generating the link.
     * @param array $attributes List of CSS attributes to output when displaying the item.
     */
    public function __construct($name, $label = null, $route = null, $parameters = array(), $attributes = array(), $icon = 'list-alt')
    {
        $this->name         = $name;
        $this->label        = $label ?: $name;
        $this->route        = $route;
        $this->parameters   = $parameters;
        $this->attributes   = $attributes;
        $this->icon         = $icon;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }

    /**
     * @return string Name of the item. Used as a key in associative arrays.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string Name of the item. Used as a key in associative arrays.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string Name of the item. Used as a key in associative arrays.
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string The icon to be displayed with this link. Must correspond to a twitter bootstrap icon-xxxx.
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string The route the item should link to.
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set a parent.
     *
     * @param $parent The parent Item to add.
     * @return Item Returns self for fluid purposes.
     */
    public function setParent(Item $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Item the parent item.
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Adds a child.
     *
     * @param $item The Item child to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addChild(Item $item)
    {
        $this->childs[] = $item;

        return $this;
    }

    /**
     * Adds a list of Item.
     *
     * @param array $items The list of Item to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addChilds(array $items)
    {
        foreach ($items as $item) {
            $this->addChild($item);
        }

        return $this;
    }

    /**
     * @return array the collection of child Item.
     */
    public function getChilds()
    {
        return $this->childs;
    }

    /**
     * Resets currently stored parameters.
     *
     * @return Item Returns self for fluid purposes.
     */
    public function clearParameters()
    {
        $this->parameters = array();

        return $this;
    }

    /**
     * Adds a parameter.
     *
     * @param $name The name of the parameter to add.
     * @param $value The value of the parameter to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Adds a list of parameters.
     *
     * @param array $parameters The list of parameters to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->addParameter($name, $value);
        }

        return $this;
    }

    /**
     * @return array The item's parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Resets currently stored attributes.
     *
     * @return Item Returns self for fluid purposes.
     */
    public function clearAttributes()
    {
        $this->attributes = array();

        return $this;
    }

    /**
     * Adds an attribute.
     *
     * @param $name The name of the attribute to add.
     * @param $value The value of the attribute to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Adds a list of attributes.
     *
     * @param array $attributes The list of attributes to add.
     * @return Item Returns self for fluid purposes.
     */
    public function addAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->addAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @return array The item's attributes.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
