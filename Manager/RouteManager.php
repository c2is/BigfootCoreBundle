<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader;
use AppKernel;

/**
 * Uses the Symfony2 route loader to store a specific set of routes.
 *
 * The routes made available through this service are those for which a "label" option is set.
 * For your routes to be available, you must use the RouteManager::addBundle and pass the bundle name (eg: "BigfootCoreBundle").
 * All routes defined in the Controller/ directory in that bundle for which a "label" option is set will be loaded by the RouteManager.
 *
 * RouteManager
 * @package Bigfoot\Bundle\CoreBundle\Manager
 */
class RouteManager
{
    /**
     * Route Loader
     *
     * @var object
     */
    protected $routeLoader;

    /**
     * Kernel
     *
     * @var object
     */
    protected $kernel;

    /**
     * Stores the parsed routes for performance concerns.
     *
     * @var array
     */
    protected $routes;

    /**
     * List of bundles added to be parsed.
     *
     * @var array
     */
    protected $bundles;

    /**
     * Associative array BundleName => boolean.
     * For each bundle added to the route manager, holds false by default, true if its routes already have been parsed.
     *
     * @var array
     */
    protected $loaded;

    /**
     * Construct RouteManager
     *
     * @param kernel      $kernel
     * @param routeLoader $routeLoader
     */
    public function __construct(AppKernel $kernel, DelegatingLoader $routeLoader)
    {
        $this->kernel      = $kernel;
        $this->routeLoader = $routeLoader;
        $this->bundles     = array();
        $this->routes      = array();
        $this->loaded      = array();
    }

    /**
     * For each bundle not yet loaded, calls the sf2 RouteLoader to find all routes for which a "label" option is defined, and adds them to $this->routes.
     */
    protected function loadRoutes()
    {
        $routes = $this->routes;

        foreach ($this->bundles as $bundle) {
            if (!$this->loaded[$bundle]) {
                $resource = $this->kernel->locateResource(sprintf('@%s/Controller/', $bundle));
                $routes   = array_merge($routes, $this->routeLoader->load($resource)->all());
            }
        }

        foreach($routes as $routeName => $route) {
            if (array_key_exists('label', $route->getOptions())) {
                $this->routes[$routeName] = $route;
            }
        }
    }

    /**
     * If at least one bundle is marked as not yet loaded, calls RouteManager::loadRoutes().
     *
     * @return array The parsed routes.
     */
    public function getRoutes()
    {
        if (in_array(false, $this->loaded)) {
            $this->loadRoutes();
        }

        return $this->routes;
    }

    /**
     * @return array The parsed routes as a name => label associative array to ease use in forms.
     */
    public function getArrayRoutes()
    {
        $tabRoutes = array();

        foreach ($this->getRoutes() as $key => $route) {
            if ($label = $route->getOption('label')) {
                $tabRoutes[$key] = $label;
            }
        }

        return $tabRoutes;
    }

    /**
     * Adds a bundle in the "to be parsed" list.
     * Sets $this->loaded to false for that bundle to force the parsing in subsequent calls to RouteManager::getRoutes()
     *
     * @param $bundleName The full bundle name to be parsed.
     * @return $this
     */
    public function addBundle($bundleName)
    {
        $this->bundles[]           = $bundleName;
        $this->loaded[$bundleName] = false;

        return $this;
    }
}