<?php

namespace Bigfoot\Bundle\CoreBundle\Route;

class RouteManager
{
    protected $container;

    protected $routes;

    public function __construct($container)
    {
        $this->container = $container;

        $routeLoader = $this->container->get('routing.loader');
        $routes = array();
        foreach ($this->container->getParameter('bigfoot.routes.paths') as $path) {
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
        return $this->routes;
    }
}