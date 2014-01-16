<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * Get Entity Manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Is Granted
     *
     * @param string $state state of the user
     *
     * @return boolean
     */
    protected function isGranted($role)
    {
        return $this->getSecurity()->isGranted($state);
    }

    /**
     * Get Repository
     *
     * @param string $class the entity class
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getRepository($class)
    {
        return $this->getEntityManager()->getRepository($class);
    }

    /**
     * Add Flash
     *
     * @param string $type type
     * @param string $text text
     */
    protected function addFlash($type, $text)
    {
        $this->get('session')->getFlashBag()->add($type, $text);
    }
}
