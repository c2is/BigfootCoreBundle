<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Bigfoot\Bundle\CoreBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
    public function __construct($em, $reader, $propertyAccessor = null)
    {
        $this->reader           = $reader;
        $this->em               = $em;
        $this->propertyAccessor = $propertyAccessor ? : PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param object $entity
     * @param string $field
     * @param string $locale
     * @param mixed $fieldData
     * @throws \Bigfoot\Bundle\CoreBundle\Exception\InvalidArgumentException
     */
    public function translate($entity, $field, $locale, $fieldData)
    {
        $em                                = $this->em;
        $meta                              = $em->getClassMetadata(get_class($entity));
        $listener                          = $this->getTranslatableListener();
        $persistDefaultLocaleTransInEntity = $listener->getPersistDefaultLocaleTranslation();

        if (is_object($entity)) {
            $entityClass = ($entity instanceof Proxy) ? get_parent_class($entity) : get_class($entity);
        } else {
            throw new InvalidArgumentException('Argument 1 passed to TranslationRepository::translate must be an object');
        }

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
                $translation = $translationClassRepository->findOneBy(
                    array(
                        'locale' => $locale,
                        'field'  => $field,
                        'object' => $entity,
                    )
                );
            }

            if ($translation) {
                $translation->setContent($fieldData);
            } elseif ($fieldData !== null) {
                $entity->addTranslation(new $entityTranslationClass($locale, $field, $fieldData));
            }
        }
    }

    /**
     * Loads all translations with all translatable
     * fields from the given entity
     *
     * @param object $entity Must implement Translatable
     *
     * @return array list of translations in locale groups
     */
    public function findTranslations($entity)
    {
        $result = array();
        $wrapped = new EntityWrapper($entity, $this->em);

        if ($wrapped->hasValidIdentifier()) {
            if (is_object($entity)) {
                $entityClass = ($entity instanceof Proxy) ? get_parent_class($entity) : get_class($entity);
            } else {
                throw new InvalidArgumentException('Argument 1 passed to TranslationRepository::translate must be an object');
            }

            $reflectionClass  = new \ReflectionClass($entityClass);
            $translationClass = $this->isPersonnalTranslationRecursive($reflectionClass)->class;

            $qb = $this->em->createQueryBuilder();
            $qb->select('trans.content, trans.field, trans.locale')
                ->from($translationClass, 'trans')
                ->where('trans.object = :object')
                ->orderBy('trans.locale');
            $q = $qb->getQuery();
            $data = $q->execute(
                array('object' => $entity),
                Query::HYDRATE_ARRAY
            );

            if ($data && is_array($data) && count($data)) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }
        }

        return $result;
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
