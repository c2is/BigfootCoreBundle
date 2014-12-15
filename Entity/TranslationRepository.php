<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Gedmo\Translatable\TranslatableListener;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class TranslationRepository
 * @package Bigfoot\Bundle\CoreBundle\Entity
 */
class TranslationRepository
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Doctrine\Common\Annotations\Reader */
    protected $reader;

    /** @var \Gedmo\Translatable\TranslatableListener */
    protected $listener;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Doctrine\Common\Annotations\Reader $reader
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct($em, $reader, $propertyAccessor)
    {
        $this->reader           = $reader;
        $this->em               = $em;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param object $entity
     * @param string $field
     * @param string $locale
     * @param mixed $fieldData
     */
    public function translate($entity, $field, $locale, $fieldData)
    {
        $em                                = $this->em;
        $meta                              = $em->getClassMetadata(get_class($entity));
        $listener                          = $this->getTranslatableListener();
        $persistDefaultLocaleTransInEntity = $listener->getPersistDefaultLocaleTranslation();
        $entityClass                       = get_class($entity);
        $reflectionClass                   = new \ReflectionClass($entityClass);
        $entityTranslationClass            = $this->isPersonnalTranslationRecursive($reflectionClass)->class;

        if ($locale === $listener->getTranslatableLocale($entity, $meta)) {
            $meta->getReflectionProperty($field)->setValue($entity, $fieldData);
            $em->persist($entity);
        } elseif (!$persistDefaultLocaleTransInEntity && $locale === $listener->getDefaultLocale()) {
            $trans = new $entityTranslationClass($locale, $field, $fieldData);

            $listener->setTranslationInDefaultLocale(spl_object_hash($entity), $field, $trans);
        } else {
            $translationClassRepository = $this->em->getRepository($entityTranslationClass);
            $meta        = $em->getClassMetadata(get_class($entity));
            $identifier  = $meta->getSingleIdentifierFieldName();
            $translation = null;

            if ($entity && $this->propertyAccessor->getValue($entity, $identifier)) {
                $translation = $translationClassRepository->findOneBy(array(
                    'locale' => $locale,
                    'field'  => $field,
                    'object' => $entity,
                ));
            }

            if ($translation) {
                $translation->setContent($fieldData);
            } elseif ($fieldData !== null) {
                $entity->addTranslation(new $entityTranslationClass($locale, $field, $fieldData));
            }
        }
    }

    /**
     * Get the currently used TranslatableListener
     *
     * @throws \Gedmo\Exception\RuntimeException - if listener is not found
     * @return \Gedmo\Translatable\TranslatableListener
     */
    private function getTranslatableListener()
    {
        if (!$this->listener) {
            foreach ($this->em->getEventManager()->getListeners() as $event => $listeners) {
                foreach ($listeners as $hash => $listener) {
                    if ($listener instanceof TranslatableListener) {
                        $this->listener = $listener;
                        break;
                    }
                }
                if ($this->listener) {
                    break;
                }
            }

            if (is_null($this->listener)) {
                throw new \Gedmo\Exception\RuntimeException('The translation listener could not be found');
            }
        }

        return $this->listener;
    }

    /**
     * @param \ReflectionClass $class
     * @return bool|null|object
     */
    public function isPersonnalTranslationRecursive(\ReflectionClass $class)
    {
        $annotationReader = $this->reader;
        if ($nodeableAnnotation = $annotationReader->getClassAnnotation($class, 'Gedmo\\Mapping\\Annotation\\TranslationEntity')) {
            return $nodeableAnnotation;
        }

        if ($parentClass = $class->getParentClass()) {
            return $this->isPersonnalTranslationRecursive($parentClass);
        }

        return false;
    }
}
