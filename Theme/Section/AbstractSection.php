<?php

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Theme\Theme;

use Symfony\Component\DependencyInjection\Container;

use Twig_Environment;
use ArrayAccess;

/**
 * Holds data to be displayed in the Bigfoot back office.
 *
 * To add data to a Section, use Section::addParameter.
 * The parameters are stored in an array accessible through the ArrayAccess interface to ease use in the twig templates.
 *
 * Class AbstractSection
 * @package Bigfoot\Bundle\CoreBundle\Theme\Section
 */
abstract class AbstractSection implements ArrayAccess
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return mixed
     */
    abstract protected function setDefaultParameters();

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->setDefaultParameters();
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     * @return $this
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     *
     */
    public function clearParameters()
    {
        $this->parameters = array();
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters = array())
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default The default value to be returned if the parameter does not exist
     * @return null
     */
    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }

    /**
     * @return array
     */
    public function getAllParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->parameters[] = $value;
        } else {
            $this->parameters[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->parameters[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset) {
        return isset($this->parameters[$offset]) ? $this->parameters[$offset] : null;
    }
}
