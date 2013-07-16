<?php

namespace Bigfoot\Bundle\CoreBundle\Route;

class RouteManager
{
    protected $container;

    protected $routes;

    protected $bundles;

    protected $loaded;

    public function __construct($container)
    {
        $this->container = $container;
        $this->bundles = array();
        $this->routes = array();
        $this->loaded = array();
    }

    protected function loadRoutes()
    {
        $routeLoader = $this->container->get('routing.loader');
        $routes = $this->routes;
        foreach ($this->bundles as $bundle) {
            if (!$this->loaded[$bundle]) {
                $resource = $this->container->get('kernel')->locateResource(sprintf('@%s/Controller/', $bundle));
                $routes = array_merge($routes, $routeLoader->load($resource)->all());
            }
        }

        foreach($routes as $routeName => $route) {
            if (array_key_exists('label', $route->getOptions())) {
                $this->routes[$routeName] = $route;
            }
        }
    }

    public function getRoutes()
    {
        if (in_array(false, $this->loaded)) {
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
        $this->loaded[$bundleName] = false;

        return $this;
    }
}