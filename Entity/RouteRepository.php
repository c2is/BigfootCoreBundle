<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * RouteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RouteRepository extends EntityRepository implements RouterInterface
{
    private $request;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRouteCollection()
    {
    }

    public function match($pathinfo)
    {
        $scheme   = $this->request->server->get('REQUEST_SCHEME');
        $httpHost = $this->request->server->get('HTTP_HOST');
        $domain   = $scheme.'://'.$httpHost;

        if ($route = $this->findOneByUrl($domain.$pathinfo)) {
            $routeObject = array(
                '_route'                  => '',
                '_route_object'           => '',
                '_controller'             => $route->getAction(),
                '_content'                => ''
            );

            if ($route->getVariableName() && $route->getForeignKey()) {
                $routeObject[$route->getVariableName()] = $route->getForeignKey();
            }

            return $routeObject;
        }

        throw new ResourceNotFoundException();
    }

    public function setContext(RequestContext $context)
    {
    }

    public function getContext()
    {
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
    }
}
