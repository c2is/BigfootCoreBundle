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
    /**
     * @var
     */
    protected $container;

    /**
     * @var
     */
    protected $title;

    protected $id;

    /**
     * @var array
     */
    protected  $params = array();

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'BigfootCoreBundle:includes:widget.html.twig';
    }

    /**
     * @return string
     */
    public abstract function renderContent();

    /**
     * @return mixed
     */
    public function render()
    {
        return $this->container->get('templating')->render($this->getTemplate(), array('widget' => $this));
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
       return  $this->title;
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
       return  $this->id;
    }

    /**
     * @param $title
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Return the value for param $name or null if not isset
     *
     * @param $name
     * @return mixed
     */
    public function getParam($name)
    {
        if ($this->hasParam($name)) {
            return $this->params[$name];
        } else {
            return null;
        }
    }

    /**
     * Return width of the widget
     *
     * @return int
     */
    public function getWidth()
    {
        // TODO : make a global parameter for default width
        return ((isset($this->params['width']) && $this->params['width'] != "") ? $this->params['width'] : 6);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }
}