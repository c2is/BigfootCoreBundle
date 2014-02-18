<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\Translator;

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
     * Persist and flush
     *
     * @param Object $entity
     */
    protected function persistAndFlush($entity)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * Remove and flush
     *
     * @param Object $entity
     */
    protected function removeAndFlush($entity)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();
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
     * Get Pagination
     *
     * @param arrayCollection $query           elements to paginate
     * @param integer         $elementsPerPage elements per page
     *
     * @return arrayCollection
     */
    protected function getPagination($query, $elementsPerPage)
    {
        return $this->get('knp_paginator')->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $elementsPerPage
        );
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

    protected function getThemeBundle()
    {
        return $this->container->getParameter('bigfoot.theme.bundle');
    }

    /**
     * Render ajax
     */
    protected function renderAjax($status, $message, $content = null, $url = null)
    {
        return new JsonResponse(
            array(
                'status'  => $status,
                'message' => $message,
                'content' => $content,
                'url'     => $url,
            )
        );
    }

    /**
     * Get Security Context
     *
     * @return Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurity()
    {
        return $this->get('security.context');
    }

    /**
     * Get Session
     *
     * @return Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * Get Templating
     *
     * @return Symfony\Component\Security\Core\SecurityContext
     */
    protected function getTemplating()
    {
        return $this->get('templating');
    }

    /**
     * Get Translator
     *
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * Get the event dispatcher
     *
     * @return Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * Get the user manager
     */
    protected function getUserManager()
    {
        return $this->get('bigfoot_user.manager.user');
    }

    /**
     * Get the menu item manager
     */
    protected function getMenuItemManager()
    {
        return $this->get('bigfoot_navigation.manager.menu_item');
    }
}
