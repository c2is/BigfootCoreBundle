<?php

namespace Bigfoot\Bundle\CoreBundle\Menu;

use Bigfoot\Bundle\UserBundle\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Bigfoot\Bundle\UserBundle\Entity\RoleMenu;

/**
 * Menu Manager
 */
class MenuManager
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var TokenStorage
     */
    private $security;

    /**
     * @var mixed
     */
    private $menu;

    /**
     * @var array
     */
    private $children;

    /**
     * @var User
     */
    private $user;

    /**
     * Constrcut
     *
     * @param FactoryInterface $factory
     * @param EntityManager $entityManager
     * @param TokenStorage $security
     */
    public function __construct(FactoryInterface $factory, EntityManager $entityManager, TokenStorage $security)
    {
        $this->factory       = $factory;
        $this->entityManager = $entityManager;
        $this->security      = $security;

        $this->repository = $this->entityManager->getRepository('BigfootUserBundle:RoleMenu');
        $this->menu       = null;
        $this->children   = array();
        $this->user       = $this->security->getToken()->getUser();
    }

    /**
     * Create root
     *
     * @param  string $slug
     * @param  array $params
     * @param  array $options
     *
     * @return MenuManager
     */
    public function createRoot($slug, $params, $options = array())
    {
        $this->menu = $this->factory->createItem($slug, $params);

        if ($this->menu->hasChildren()) {
            $this->menu->setChildren(array());
        }

        foreach ($options as $type => $option) {
            switch ($type) {
                case 'children-attributes':
                    $this->menu->setChildrenAttributes($option);
                    break;
            }
        }

        return $this;
    }

    /**
     * Delete node
     *
     * @param  string $slug
     *
     * @return mixed
     */
    public function deleteNode($slug)
    {
        return $this->delete($slug, $this->children);
    }

    /**
     * Delete
     * use recursively to delete node in tree
     *
     * @param  string $slug
     * @param  array $nodes
     *
     * @return mixed
     */
    public function delete($slug, &$nodes)
    {
        foreach ($nodes as $key => $node) {
            if ($node->slug == $slug) {
                unset($nodes[$key]);

                return $this;
            }

            if (count($node->children)) {
                $this->delete($slug, $node->children);
            }
        }

        return $this;
    }

    /**
     * Add child
     *
     * @param string $slug
     * @param array $params
     * @param array $options
     *
     * @return MenuManager
     */
    public function addChild($slug, $params, $options = array())
    {
        $child           = new \stdClass;
        $child->slug     = $slug;
        $child->params   = $params;
        $child->options  = $options;
        $child->children = array();
        $child->parent   = null;

        $this->children[$slug] = $child;

        return $this;
    }

    /**
     * Add child for
     *
     * @param string $parentSlug
     * @param string $slug
     * @param array $params
     * @param array $options
     *
     * @return MenuManager
     */
    public function addChildFor($parentSlug, $slug, $params, $options = array())
    {
        $parent = $this->nodeExists($this->children, $parentSlug);

        if (!$parent) {
            throw new \Exception("Parent node with slug " . $parentSlug . " doesn't exists");
        }

        $child           = new \stdClass;
        $child->slug     = $slug;
        $child->params   = $params;
        $child->options  = $options;
        $child->children = array();
        $child->parent   = $parentSlug;

        $parent->children[$slug] = $child;

        return $this;
    }

    /**
     * Child exists
     *
     * @param  string $slug
     *
     * @return boolean
     */
    public function childExists($slug)
    {
        return $this->nodeExists($this->children, $slug) ? true : false;
    }

    /**
     * Node exists
     *
     * @param  array $nodes
     * @param  string $slug
     *
     * @return mixed
     */
    private function nodeExists(&$nodes, $slug)
    {
        foreach ($nodes as &$node) {
            if ($node->slug == $slug) {
                return $node;
            }

            if (isset($node->children)) {
                if ($this->nodeExists($node->children, $slug)) {
                    return $this->nodeExists($node->children, $slug);
                }
            }
        }

        return false;
    }

    /**
     * Create node
     *
     * @param  stdClass $node
     *
     * @return mixed
     */
    public function createNode($node)
    {
        if ($this->menu === null) {
            throw new \Exception("Before to create a child node you need to call MenuManager::createRoot() to create the parent node menu");
        }

        if (empty($node)) {
            return;
        }

        $slug    = $node->slug;
        $params  = $node->params;
        $options = $node->options;

        if (!$this->isGranted($slug)) {
            return false;
        }

        if (!$this->menu->getChild($slug)) {
            $child = $this->menu->addChild($slug, $params);

            foreach ($options as $type => $option) {
                switch ($type) {
                    case 'children-attributes':
                        $child->setChildrenAttributes($option);
                        break;
                }
            }

            if (count($node->children)) {
                $this->createChild($child, $node->children);
            }
        }


        return $this;
    }

    /**
     * Create child
     *
     * @param  stdClass $parent
     * @param  array $children
     *
     * @return stClass
     */
    public function createChild($parent, $children)
    {
        foreach ($children as $child) {
            if ($this->isGranted($child->slug)) {
                $node = $parent->addChild($child->slug, $child->params);

                foreach ($child->options as $type => $option) {
                    switch ($type) {
                        case 'children-attributes':
                            $node->setChildrenAttributes($option);
                            break;
                    }
                }

                if (count($child->children)) {
                    $this->createChild($node, $child->children);
                }
            }
        }

        return $parent;
    }

    /**
     * Create menu
     *
     * @return MenuItem
     */
    public function createMenu()
    {
        foreach ($this->children as $child) {
            $this->createNode($child);
        }

        return $this->menu;
    }

    /**
     * Is granted
     *
     * @param  string $slug
     *
     * @return boolean
     */
    private function isGranted($slug)
    {
        $roles = $this->user->getRoles();

        /** @var Role $role */
        if (in_array('ROLE_ADMIN', $roles)) {
            return true;
        }

        $granted  = false;
        $roleMenu = $this->repository->findOneBySlug($slug);

        if ($roleMenu instanceof RoleMenu) {
            if (count($roleMenu->getRoles())) {
                $granted = false;
                foreach ($roleMenu->getArrayRoles() as $role) {
                    if (in_array($role, $roles)) {
                        $granted = true;
                    }
                }
            }
        }

        return $granted;
    }
}
