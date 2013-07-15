<?php

namespace Bigfoot\Bundle\CoreBundle\Route;

class RouteManager
{
    protected $container;

    protected $routes;

    protected $bundles;

    public function __construct($container)
    {
        $this->container = $container;
        $this->bundles = array();
        $this->routes = array();
    }

    protected function loadRoutes()
    {
        $routeLoader = $this->container->get('routing.loader');
        $routes = array();
        foreach ($this->bundles as $path) {
            $resource = $this->container->get('kernel')->locateResource(sprintf('@%s/Controller/', $path));
            $routes = array_merge($routes, $routeLoader->load($resource)->all());
        }

        foreach($routes as $routeName => $route) {
            if (array_key_exists('label', $route->getOptions())) {
                $this->routes[$routeName] = $route;
            }
        }
    }

    public function getRoutes()
    {
        if (!count($this->routes)) {
            $this->loadRoutes();
        }

        return $this->routes;
    }

    public function getArrayRoutes()
    {
        $tabRoutes = array();

        foreach ($this->getRoutes() as $key => $route){
            if ($label = $route->getOption('label')){
                $tabRoutes[$key] = $label;
            }
        }

        return $tabRoutes;
    }

    public function addBundle($bundleName)
    {
        $this->bundles[] = $bundleName;

        return $this;
    }
}