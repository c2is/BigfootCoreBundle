<?php

namespace Bigfoot\Bundle\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Bigfoot\Bundle\CoreBundle\Manager\FileManager;

use Doctrine\ORM\EntityManager;

/**
 * Class FileUploadListener
 * @package Bigfoot\Bundle\CoreBundle\Listener
 */
class DoctrineListener
{
    /** @var Bigfoot\Bundle\CoreBundle\Manager\FileManager  */
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $this->preUpload($args);
    }

    /**
     * @param PreFlushEventArgs $args
     */
    private function preUpload(PreFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $entities = array();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $entities[] = $entity;
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $entities[] = $entity;
        }

        foreach ($entities as $entity) {
            $bigfootFileFields = $this->getBigfootFileFields($entity);
            foreach ($bigfootFileFields as $bigfootFileField) {
                $this->fileManager->preUpload($entity, $bigfootFileField['filePathProperty'], $bigfootFileField['fileFieldProperty']);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->upload($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->upload($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $bigfootFileFields = $this->getBigfootFileFields($entity);
        foreach ($bigfootFileFields as $bigfootFileField) {
            $this->fileManager->deleteFile($entity, $bigfootFileField['filePathProperty']);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function upload(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $bigfootFileFields = $this->getBigfootFileFields($entity);
        foreach ($bigfootFileFields as $bigfootFileField) {
            $this->fileManager->upload($entity, $bigfootFileField['filePathProperty'], $bigfootFileField['fileFieldProperty']);
        }
    }

    private function getBigfootFileFields($entity)
    {
        $reader            = new AnnotationReader();
        $reflClass         = new \ReflectionClass(get_class($entity));
        $classProperties   = $reflClass->getProperties();
        $bigfootFileFields = array();

        foreach ($classProperties as $property) {
            $classAnnotations = $reader->getPropertyAnnotations($property);
            foreach ($classAnnotations as $annot) {
                if ($annot instanceof \Bigfoot\Bundle\CoreBundle\Annotation\Bigfoot\File) {
                    $bigfootFileFields[] = array(
                        'fileFieldProperty' => $property->getName(),
                        'filePathProperty' => $annot->getFilePathProperty()
                    );
                }
            }
        }
        return $bigfootFileFields;
    }
}
