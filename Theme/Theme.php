<?php

namespace Bigfoot\Bundle\CoreBundle\Theme;

use Symfony\Component\DependencyInjection\Container;

use Bigfoot\Bundle\CoreBundle\Theme\Section;

use Assetic\Asset\AssetCache;
use Assetic\Asset\BaseCache;

use ArrayAccess;

class Theme implements ArrayAccess
{
    protected $sections = array();

    protected $namespace = '';

    public function __construct(Container $container, Section\ToolbarSection $toolbar, Section\HeaderSection $header, Section\SidebarSection $sidebar, Section\PageHeaderSection $pageHeader, Section\PageContentSection $pageContent, Section\FooterSection $footer)
    {
        $header->setParameter('title', $container->getParameter('bigfoot.theme.sitename'));
        $header->setParameter('subtitle', $container->getParameter('bigfoot.theme.backend.name'));

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

    public function setTwigNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getTwigNamespace()
    {
        return $this->namespace;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->sections[] = $value;
        } else {
            $this->sections[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->sections[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->sections[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->sections[$offset]) ? $this->sections[$offset] : null;
    }
}
