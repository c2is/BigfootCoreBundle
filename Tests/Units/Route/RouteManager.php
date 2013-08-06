<?php

namespace Bigfoot\Bundle\CoreBundle\Tests\Units\Route;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use atoum\AtoumBundle\Test\Units;

/**
 * Class RouteManager
 * @package Bigfoot\Bundle\CoreBundle\Tests\Units\Route
 */
class RouteManager extends Units\Test
{
    public function testGetRoutes()
    {
        $routeManager = new \Bigfoot\Bundle\CoreBundle\Route\RouteManager($this->getMockContainer());

        $this
            ->array($routeManager->getRoutes())
                ->isEmpty();

        $routeManager->addBundle('Foo');

        $routes = $routeManager->getRoutes();
        $this
            ->array($routes)
                ->hasKey('foo')
                ->hasSize(1)
            ->object($routes['foo'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route');

        $routeManager->addBundle('Bar');

        $routes = $routeManager->getRoutes();
        $this
            ->array($routes)
                ->hasKey('foo')
                ->hasKey('bar1')
                ->hasKey('bar2')
                ->hasSize(3)
            ->object($routes['foo'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route')
            ->object($routes['bar1'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route')
            ->object($routes['bar2'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route');

        $routeManager->addBundle('FooBar');

        $routes = $routeManager->getRoutes();
        $this
            ->array($routes)
                ->hasKey('foo')
                ->hasKey('bar1')
                ->hasKey('bar2')
                ->hasSize(3)
            ->object($routes['foo'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route')
            ->object($routes['bar1'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route')
            ->object($routes['bar2'])
                ->isInstanceOf('\\Symfony\\Component\\Routing\\Route');
    }

    public function testGetArrayRoutes()
    {
        $routeManager = new \Bigfoot\Bundle\CoreBundle\Route\RouteManager($this->getMockContainer());

        $fooReturn = array(
            'foo' => 'Foo route',
        );
        $foobarReturn = array(
            'foo' => 'Foo route',
            'bar1' => 'Bar route 1',
            'bar2' => 'Bar route 2',
        );

        $this
            ->array($routeManager->getArrayRoutes())
                ->isEmpty();

        $routeManager->addBundle('Foo');

        $this
            ->array($routeManager->getArrayRoutes())
                ->isEqualTo($fooReturn);

        $routeManager->addBundle('Bar');

        $this
            ->array($routeManager->getArrayRoutes())
                ->isEqualTo($foobarReturn);
    }

    private function getMockContainer()
    {
        $container = new \mock\Symfony\Component\DependencyInjection\Container;

        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $routeLoader = new \mock\Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader();
        $this->calling($routeLoader)->load = function ($resource) {
            $toReturn = new RouteCollection();

            if ($resource == 'foo') {
                $routeFoo = new Route('/foo', array(), array(), array(
                    'label' => 'Foo route',
                ));
                $toReturn->add('foo', $routeFoo);
                $routeBar = new Route('/bar', array(), array(), array());
                $toReturn->add('bar', $routeBar);
            }

            if ($resource == 'bar') {
                $routeFoo = new Route('/foo', array(), array(), array());
                $toReturn->add('foo', $routeFoo);
                $routeBar1 = new Route('/bar1', array(), array(), array(
                    'label' => 'Bar route 1',
                ));
                $toReturn->add('bar1', $routeBar1);
                $routeBar2 = new Route('/bar2', array(), array(), array(
                    'label' => 'Bar route 2',
                ));
                $toReturn->add('bar2', $routeBar2);
            }

            return $toReturn;
        };

        $kernel = new \mock\AppKernel();
        $this->calling($kernel)->locateResource = function ($path) {
            if ($path == '@Foo/Controller/') {
                return 'foo';
            }

            if ($path == '@Bar/Controller/') {
                return 'bar';
            }

            return '';
        };

        $this->calling($container)->get = function ($name) use ($routeLoader, $kernel) {
            if ($name == 'routing.loader') {
                return $routeLoader;
            }

            if ($name == 'kernel') {
                return $kernel;
            }

            throw new ServiceNotFoundException($name);
        };

        return $container;
    }
}