<?php

/*
 * This file is part of the c2is/silex-bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 * (c) Guillaume Manen <g.manen@c2is.fr>
 */

namespace Bigfoot\Bundle\CoreBundle\Theme\Section;

use Bigfoot\Bundle\CoreBundle\Theme\Theme;

use Symfony\Component\DependencyInjection\Container;

use Twig_Environment;
use ArrayAccess;

abstract class AbstractSection implements ArrayAccess
{
    protected $container;

    protected $view;

    protected $theme;

    protected $parameters = array();

    abstract public function getName();

    abstract protected function setDefaultParameters();

    public function __construct($container)
    {
        $this->container = $container;

        $this->setDefaultParameters();
    }

    public function getView()
    {
        return $this->view ?: sprintf('%s:theme:%s.html.twig', $this->theme->getTwigNamespace(), $this->getName());
    }

    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    public function clearParameters()
    {
        $this->parameters = array();
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function setParameters(array $parameters = array())
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }

    public function getAllParameters()
    {
        return $this->parameters;
    }

    public function render()
    {
        $view = $this->getView();

        return $this->container->get('twig')->render($view, $this->parameters);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->parameters[] = $value;
        } else {
            $this->parameters[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->parameters[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->parameters[$offset]) ? $this->parameters[$offset] : null;
    }
}
