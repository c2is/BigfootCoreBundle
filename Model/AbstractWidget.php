<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 22/01/14
 * Time: 11:55
 */

namespace Bigfoot\Bundle\CoreBundle\Model;

abstract class AbstractWidget
{
    protected $container;

    protected $title;

    protected  $params = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    protected function getTemplate()
    {
        return 'BigfootCoreBundle:includes:widget.html.twig';
    }

    public abstract function renderContent();

    public function render()
    {
        return $this->container->get('templating')->render($this->getTemplate(), array('widget' => $this));
    }

    public function getTitle()
    {
       return  $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function getParam($name)
    {
        return $this->params[$name];
    }

    public function getWidth()
    {
        return ((isset($this->params['width']) && $this->params['width'] != "") ? $this->params['width'] : 6);
    }
}