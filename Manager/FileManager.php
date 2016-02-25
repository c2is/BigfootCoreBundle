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
     * File preUpload : delete old file if already exists and sets file name into $entity->$pathProperty
     * @throws \Exception If $pathProperty or $fileFieldProperty getter and setter don't exist on class
     */
    public function preUpload($entity, $pathProperty, $fileFieldProperty)
    {
        $setFileFieldFunction = 'get'.ucfirst($fileFieldProperty);
        $setPathFunction = 'set'.ucfirst($pathProperty);
        $getPathFunction = 'get'.ucfirst($pathProperty);

        if (method_exists($entity, $setFileFieldFunction) &&
            method_exists($entity, $getPathFunction) &&
            method_exists($entity, $setPathFunction )) {
            if (null !== $entity->$setFileFieldFunction()) {

                if ($file = $this->getFileAbsolutePath($entity, $pathProperty)) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                $entity->$setPathFunction(uniqid().'_'.$this->sanitizeName($entity->$setFileFieldFunction()->getClientOriginalName()));
            }
        } else {
            throw new \Exception("Methods '".$setFileFieldFunction."' and '".$setPathFunction."' and '".$getPathFunction."' should be defined on '".get_class($entity)."' class");
        }
    }

    /**
     * Manage file's upload (create directory and move temporary file to entity's upload directory)
     * @throws \Exception If $property getter and setter do'nt exist on class
     */
    public function upload($entity, $pathProperty, $fileFieldProperty)
    {
        $setFileFieldFunction = 'get'.ucfirst($fileFieldProperty);
        $getPathFunction = 'get'.ucfirst($pathProperty);

        if (method_exists($entity, $setFileFieldFunction) && method_exists($entity, $getPathFunction)) {
            if (null === $entity->$setFileFieldFunction()) {
                return;
            }

            if (!is_dir($this->getUploadDir($entity))) {
                mkdir($this->getUploadDir($entity), 0777);
            }

            $entity->$setFileFieldFunction()->move($this->getUploadDir($entity), $entity->$getPathFunction());

        } else {
            throw new \Exception("Methods '".$setFileFieldFunction."' and '".$getPathFunction."' should be defined on '".get_class($entity)."' class");
        }
    }

    /**
     * Get the file absolute path
     * @return string file path
     * @throws \Exception If $property getter does not exists on class
     */
    public function getFileAbsolutePath($entity, $pathProperty)
    {
        $getPathFunction = 'get'.ucfirst($pathProperty);
        if (method_exists($entity, $getPathFunction)) {
            if (!$entity->$getPathFunction()) {
                return null;
            } else {
                return $this->getUploadDir($entity) . $entity->$getPathFunction();
           }
       } else {
            throw new \Exception("Methods '".$getPathFunction." should be defined on '".get_class($entity)."' class");
        }
    }

    /**
     * Get the upload directory
     * @return string absolute directory path
     */
    private function getUploadDir($entity)
    {
        $webDir    = $this->container->get('kernel')->getRootDir() . '/../web/';
        $uploadDir = $this->container->getParameter('bigfoot.core.upload_dir');
        $reflClass = new \ReflectionClass(get_class($entity));
        $entityDir = strtolower($reflClass->getShortName()) . '/';
        return $webDir . $uploadDir . $entityDir;
    }

    /**
     * Get the file path in order to display the file in browser
     * @return string file path
     * @throws \Exception If $property getter does not exists on class
     */
    public function getFilePath($entity, $pathProperty)
    {
        $getPathFunction = 'get'.ucfirst($pathProperty);
        if (method_exists($entity, $getPathFunction)) {
            if (!$entity->$getPathFunction()) {
                return null;
            } else {
                $uploadDir = $this->container->getParameter('bigfoot.core.upload_dir');
                $reflClass = new \ReflectionClass(get_class($entity));
                $entityDir = strtolower($reflClass->getShortName()) . '/';
                return '/'.$uploadDir . $entityDir . $entity->$getPathFunction();
            }
        } else {
            throw new \Exception("Methods '".$getPathFunction." should be defined on '".get_class($entity)."' class");
        }
    }

    /**
     * Delete file
     * @param  Entity $entity
     * @param  string $pathProperty
     * @return boolean
     */
    public function deleteFile($entity, $pathProperty)
    {
        $file = $this->getFileAbsolutePath($entity, $pathProperty);

        if ($file && file_exists($file) && !is_dir($file)) {
            return unlink($file);
        } else {
            return false;
        }
    }

    /**
     * Escape text in order to get proper file names
     * @param  string $text
     * @return string
     */
    public function sanitizeName($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\\pL\d.]+~u', '-', $text);

      // trim
      $text = trim($text, '-');

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // lowercase
      $text = strtolower($text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w.]+~', '', $text);

      return $text;
    }
}
