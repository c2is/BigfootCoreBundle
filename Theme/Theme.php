<?php

namespace Bigfoot\Bundle\CoreBundle\Theme;

use Symfony\Component\DependencyInjection\Container;

use Bigfoot\Bundle\CoreBundle\Theme\Section;

use Assetic\Asset\AssetCache;
use Assetic\Asset\BaseCache;

use ArrayAccess;

/**
 * The theme service holds data to be used when displaying a page in the Bigfoot back office.
 * Allows bundles to inject textual data or menu items into the back office interface.
 *
 * The theme is composed of sections that serve to encapsulate data into logically relevant areas (for example, everything to be displayed in the sidebar will be held in the Sidebar section)
 *
 * Implements the ArrayAccess interface to allow array fetching of sections.
 *
 * Class Theme
 * @package Bigfoot\Bundle\CoreBundle\Theme
 */
class Theme implements ArrayAccess
{
    /**
     * @var array
     */
    protected $sections = array();

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @param Container $container
     * @param Section\ToolbarSection $toolbar
     * @param Section\HeaderSection $header
     * @param Section\SidebarSection $sidebar
     * @param Section\PageHeaderSection $pageHeader
     * @param Section\PageContentSection $pageContent
     * @param Section\FooterSection $footer
     */
    public function __construct(Container $container, Section\ToolbarSection $toolbar, Section\HeaderSection $header, Section\SidebarSection $sidebar, Section\PageHeaderSection $pageHeader, Section\PageContentSection $pageContent, Section\FooterSection $footer)
    {
        $themeValues = $container->getParameter('bigfoot.theme.values');
        $header->setParameter('title', $themeValues['title']);
        $header->setParameter('subtitle', $themeValues['subtitle']);

        $sections = array();
        $sections[] = $toolbar;
        $sections[] = $header;
        $sections[] = $sidebar;
        $sections[] = $pageHeader;
        $sections[] = $pageContent;
        $sections[] = $footer;

        foreach ($sections as $section) {
            $section->setTheme($this);

            $this->sections[$section->getName()] = $section;
        }
    }

    /**
     * @param $namespace
     * @return $this
     */
    public function setTwigNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwigNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->sections[] = $value;
        } else {
            $this->sections[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->sections[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->sections[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset) {
        return isset($this->sections[$offset]) ? $this->sections[$offset] : null;
    }
}
