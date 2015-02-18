<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager;

/**
 * File Manager
 */
class FileManager extends ContainerAware
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private $entity;

    /**
     * Property's name from the entity that correspond to the uploadedFile
     * @var string
     */
    private $property;

    /**
     * Property's name from the entity that stores the actual filename
     * @var string
     */
    private $relatedProperty;


    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * [initialize description]
     * @param  mixed $entity
     * @param  string $relatedPropertyName
     * @param  string $propertyName
     */
    public function initialize($entity, $relatedPropertyName, $propertyName = null)
    {
        $this->setEntity($entity);
        $this->setProperty($propertyName);
        $this->setRelatedProperty($relatedPropertyName);
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function setRelatedProperty($relatedProperty)
    {
        $this->relatedProperty = $relatedProperty;
    }

    /**
     * File preUpload : delete old file if already exists and sets file name into $entity->$relatedProperty
     * @throws \Exception If $property getter and setter do'nt exist on class
     */
    public function preUpload()
    {
        $getPropertyFunction = 'get'.ucfirst($this->property);
        $setRelatedPropertyFunction = 'set'.ucfirst($this->relatedProperty);
        $getRelatedPropertyFunction = 'get'.ucfirst($this->relatedProperty);

        if (method_exists($this->entity, $getPropertyFunction) &&
            method_exists($this->entity, $getRelatedPropertyFunction) &&
            method_exists($this->entity, $setRelatedPropertyFunction )) {
            if (null !== $this->entity->$getPropertyFunction()) {

                if ($file = $this->getFileAbsolutePath()) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                $this->entity->$setRelatedPropertyFunction(sha1(uniqid(mt_rand(), true)).'_'.str_replace(' ', '-', $this->entity->$getPropertyFunction()->getClientOriginalName()));
            }
        } else {
            throw new \Exception("Methods '".$getPropertyFunction."' and '".$setRelatedPropertyFunction."' and '".$getRelatedPropertyFunction."' should be defined on '".get_class($this->entity)."' class");
        }
    }

    /**
     * Manage file's upload (create directory and move temporary file to entity's upload directory)
     * @throws \Exception If $property getter and setter do'nt exist on class
     */
    public function upload()
    {
        $getPropertyFunction = 'get'.ucfirst($this->property);
        $getRelatedPropertyFunction = 'get'.ucfirst($this->relatedProperty);

        if (method_exists($this->entity, $getPropertyFunction) && method_exists($this->entity, $getRelatedPropertyFunction)) {
            if (null === $this->entity->$getPropertyFunction()) {
                return;
            }

            if (!is_dir($this->getUploadDir())) {
                mkdir($this->getUploadDir(), 0777);
            }

            $this->entity->$getPropertyFunction()->move($this->getUploadDir(), $this->entity->$getRelatedPropertyFunction());

        } else {
            throw new \Exception("Methods '".$getPropertyFunction."' and '".$getRelatedPropertyFunction."' should be defined on '".get_class($this->entity)."' class");
        }
    }

    /**
     * Get the file absolute path
     * @return string file path
     * @throws \Exception If $property getter does not exists on class
     */
    public function getFileAbsolutePath()
    {
        $getRelatedPropertyFunction = 'get'.ucfirst($this->relatedProperty);
        if (method_exists($this->entity, $getRelatedPropertyFunction)) {
            if (!$this->entity->$getRelatedPropertyFunction()) {
                return null;
            } else {
                return $this->getUploadDir() . $this->entity->$getRelatedPropertyFunction();
           }
       } else {
            throw new \Exception("Methods '".$getRelatedPropertyFunction." should be defined on '".get_class($this->entity)."' class");
        }
    }

    /**
     * Get the upload directory
     * @return string absolute directory path
     */
    private function getUploadDir()
    {
        $webDir    = $this->container->get('kernel')->getRootDir() . '/../web/';
        $uploadDir = $this->container->getParameter('bigfoot.core.upload_dir');
        $reflClass = new \ReflectionClass(get_class($this->entity));
        $entityDir = strtolower($reflClass->getShortName()) . '/';
        return $webDir . $uploadDir . $entityDir;
    }

    /**
     * Get the file path in order to display the file in browser
     * @return string file path
     * @throws \Exception If $property getter does not exists on class
     */
    public function getFilePath()
    {
        $getRelatedPropertyFunction = 'get'.ucfirst($this->relatedProperty);
        if (method_exists($this->entity, $getRelatedPropertyFunction)) {
            if (!$this->entity->$getRelatedPropertyFunction()) {
                return null;
            } else {
                $uploadDir = $this->container->getParameter('bigfoot.core.upload_dir');
                $reflClass = new \ReflectionClass(get_class($this->entity));
                $entityDir = strtolower($reflClass->getShortName()) . '/';
                return '/'.$uploadDir . $entityDir . $this->entity->$getRelatedPropertyFunction();
            }
        } else {
            throw new \Exception("Methods '".$getRelatedPropertyFunction." should be defined on '".get_class($this->entity)."' class");
        }
    }
}
