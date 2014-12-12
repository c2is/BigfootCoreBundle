<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\ContextBundle\Entity\ContextRepository;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Paginator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Bigfoot\Bundle\CoreBundle\Event\FormEvent;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * Get Entity Manager
     *
     * @return EntityManager
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
        return $this->getSecurity()->isGranted($role);
    }

    /**
     * Get Repository
     *
     * @param string $class the entity class
     *
     * @return EntityRepository
     */
    protected function getRepository($class)
    {
        return $this->getEntityManager()->getRepository($class);
    }

    /**
     * Get Pagination
     *
     * @param Query   $query
     * @param integer $elementsPerPage elements per page
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    protected function getPagination($query, $elementsPerPage)
    {
        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');

        return $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $elementsPerPage,
            array('distinct' => false)
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
     * Create form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        $form = parent::createForm($type, $data, $options);

        $this->getEventDispatcher()->dispatch(FormEvent::CREATE, new GenericEvent($form));

        return $form;
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
     * @return SecurityContext
     */
    protected function getSecurity()
    {
        return $this->get('security.context');
    }

    /**
     * Get Session
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * Get Router
     *
     * @return SecurityContext
     */
    protected function getRouter()
    {
        return $this->get('router');
    }


    /**
     * Get Templating
     *
     * @return SecurityContext
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
     * @return EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * Get the bigfoot context
     */
    protected function getContext()
    {
        return $this->get('bigfoot_context');
    }

    /**
     * Get the bigfoot context manager
     */
    protected function getContextManager()
    {
        return $this->get('bigfoot_context.manager.context');
    }

    /**
     * Get the bigfoot settings manager
     */
    protected function getSettingsManager()
    {
        return $this->get('bigfoot_core.manager.settings');
    }

    /**
     * Get the user manager
     */
    protected function getUserManager()
    {
        return $this->get('bigfoot_user.manager.user');
    }

    /**
     * Get the context repository
     * @return ContextRepository
     */
    protected function getContextRepository()
    {
        return $this->get('bigfoot_context.repository.context');
    }

    /**
     * @return int
     */
    protected function getElementsPerPage()
    {
        return 50;
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function getFormErrorsAsArray(Form $form)
    {
        return array_merge($this->getErrorsArray($form), $this->getErrorsFromSubForm($form, $form->getName()));
    }

    /**
     * @param Form $form The form subject to validation
     * @param string $prefix The field name prefix (concatenation of parents names).
     * @return array
     */
    private function getErrorsFromSubForm(Form $form, $prefix)
    {
        $errors = array();
        foreach ($form->all() as $child) {
            $errors = array_merge($errors, $this->getErrorsArray($child, $prefix), $this->getErrorsFromSubForm($child, sprintf('%s[%s]', $prefix, $child->getName())));
        }

        return $errors;
    }

    /**
     * @param Form $form The form
     * @param string $namePrefix The field name prefix (concatenation of parents names).
     * @return array The form errors, as an array
     */
    private function getErrorsArray(Form $form, $namePrefix = '')
    {
        $formName = $namePrefix ? sprintf('%s[%s]', $namePrefix, $form->getName()) : $form->getName();

        $errors = array();
        foreach ($form->getErrors() as $error) {
            if (!isset($errors[$formName])) {
                $errors[$formName] = array();
            }
            $errors[$formName][] = array(
                'field' => $form->getName(),
                'message' => $error->getMessage(),
            );
        }

        return $errors;
    }
}
