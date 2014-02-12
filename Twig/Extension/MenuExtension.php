<?php

namespace Bigfoot\Bundle\CoreBundle\Twig\Extension;

use Twig_Extension;
use Twig_Function_Method;
use Knp\Menu\Twig\Helper;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\Iterator\CurrentItemFilterIterator;
use Knp\Menu\Util\MenuManipulator;

class MenuExtension extends Twig_Extension
{
    private $helper;
    private $matcher;

    /**
     * Construct MenuExtension
     *
     * @param Helper  $helper
     * @param Matcher $matcher
     */
    public function __construct(Helper $helper, Matcher $matcher)
    {
        $this->helper  = $helper;
        $this->matcher = $matcher;
    }

    public function getFunctions()
    {
        return array(
            'knp_breadcrumb_render' => new Twig_Function_Method(
                $this,
                'getBreadcrumb',
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param ItemInterface|string|array $menu
     *
     * @return string
     */
    public function getBreadcrumb($menu, $actions = null)
    {
        $menu = $this->helper->get($menu);

        $treeIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator(
                new \ArrayIterator(array($menu))
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator    = new CurrentItemFilterIterator($treeIterator, $this->matcher);
        $manipulator = new MenuManipulator();

        foreach ($iterator as $item) {
            return $manipulator->getBreadcrumbsArray($item);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_menu';
    }
}
