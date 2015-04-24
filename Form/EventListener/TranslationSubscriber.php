<?php

namespace Bigfoot\Bundle\CoreBundle\Form\EventListener;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Doctrine\Common\Annotations\Reader;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository as BigfootTranslationRepository;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TranslationSubscriber
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\EventListener
 */
class TranslationSubscriber implements EventSubscriberInterface
{
    /** @var array */
    protected $localeList;
    /** @var \Symfony\Bridge\Doctrine\RegistryInterface */
    protected $doctrine;
    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;
    /** @var string */
    protected $defaultLocale;
    /** @var string */
    protected $currentLocale;
    /** @var BigfootTranslationRepository */
    protected $translationRepository;

    /**
     * @param array                        $localeList
     * @param RegistryInterface            $doctrine
     * @param Reader                       $annotationReader
     * @param BigfootTranslationRepository $translationRepository
     * @param string                       $defaultLocale
     * @param ContextService               $context
     */
    public function __construct(
        $localeList,
        RegistryInterface $doctrine,
        Reader $annotationReader,
        BigfootTranslationRepository $translationRepository,
        $defaultLocale,
        ContextService $context
    ) {
        $this->localeList            = $localeList;
        $this->doctrine              = $doctrine;
        $this->annotationReader      = $annotationReader;
        $this->translationRepository = $translationRepository;
        $this->defaultLocale         = $defaultLocale;
        $this->currentLocale         = $context->getDefaultFrontLocale();
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->currentLocale ?: $this->defaultLocale;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData', FormEvents::POST_SUBMIT => array('postSubmit', -500));
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $em         = $this->doctrine->getManager();
        $locales    = $this->localeList;
        $form       = $event->getForm();
        $parentForm = $form->getParent();
        $parentData = $parentForm->getData();
        $listener   = $this->getTranslatableListener();

        if ($parentData) {
            $entityClass = get_class($parentData);
        } else {
            $entityClass = $parentForm->getConfig()->getDataClass();
        }

        $meta = $this->doctrine->getManager()->getClassMetadata($entityClass);

        if ($entityClass) {
            $translatableFields = $this->getTranslatableFields($entityClass);
            $propertyAccessor   = PropertyAccess::createPropertyAccessor();
            $translations       = array();
            $initialLocale      = ($parentData) ? $listener->getTranslatableLocale($parentData, $meta) : $this->defaultLocale;
            unset($locales[$initialLocale]);

            $form->add('_entity_locale', 'hidden', array(
                'data' => $initialLocale,
                'mapped' => false,
                'attr' => array(
                    'class' => 'entity-locale',
                ),
            ));

            if ($parentData and method_exists($parentData, 'getId') and $parentData->getId()) {
                $translations = array();

                foreach ($locales as $locale => $localeConfig) {
                    $localeValues = array();
                    $parentData->setTranslatableLocale($locale);
                    $em->refresh($parentData);

                    foreach ($translatableFields as $fieldName => $fieldType) {
                        $localeValues[$fieldName] = $propertyAccessor->getValue($parentData, $fieldName);
                    }

                    $translations[$locale] = $localeValues;
                }

                $parentData->setTranslatableLocale($initialLocale);
                $em->refresh($parentData);
            }

            foreach ($locales as $locale => $localeConfig) {
                foreach ($translatableFields as $fieldName => $fieldType) {
                    $data = '';

                    if (isset($translations[$locale][$fieldName])) {
                        $data = $translations[$locale][$fieldName];
                    }

                    if ($parentForm->has($fieldName)) {
                        $fieldType = $parentForm->get($fieldName)->getConfig()->getType()->getInnerType();
                        $fieldAttr = $parentForm->get($fieldName)->getConfig()->getOption('attr');
                        $form->add(
                            sprintf('%s-%s', $fieldName, $locale),
                            $fieldType,
                            array(
                                'data'     => $data,
                                'required' => false,
                                'attr'     => array_merge(
                                    $fieldAttr,
                                    array('data-field-name' => $fieldName, 'data-locale' => $locale)
                                )
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form             = $event->getForm();
        $parentForm       = $form->getParent();
        $parentData       = $parentForm->getData();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($parentData) {
            $entityClass        = get_class($parentData);
            $em                 = $this->doctrine->getManagerForClass($entityClass);
            $translatableFields = $this->getTranslatableFields($entityClass);
            $data               = $event->getData();
            $locales            = $this->localeList;

            $reflectionClass  = new \ReflectionClass($entityClass);
            $gedmoAnnotations = $this->isPersonnalTranslationRecursive($reflectionClass);

            if ($gedmoAnnotations && is_object($gedmoAnnotations) && $gedmoAnnotations->class != '') {
                $repository = $this->translationRepository;
            } else {
                $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
            }

            foreach ($locales as $locale => $localeConf) {
                foreach ($translatableFields as $field => $type) {
                    if ($parentForm->has($field)) {
                        $fieldData       = '';
                        $localeFieldName = sprintf('%s-%s', $field, $locale);

                        if (isset($data[$localeFieldName])) {
                            $fieldData = $data[$localeFieldName];
                        } elseif (isset($data[$field])) {
                            $fieldData = $data[$field];
                        }

                        if ($field != 'slug' || $fieldData) {
                            if ($repository instanceof BigfootTranslationRepository && $this->currentLocale == $locale) {
                                $fieldData = $propertyAccessor->getValue($parentData, $field);
                            }

                            $repository->translate($parentData, $field, $locale, $fieldData);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns an array containing all attributes from the given entity and all its eventual inherited parent entities
     * for which a Gedmo\Translatable annotation is set
     *
     * @param string $className
     *
     * @return array
     */
    private function getTranslatableFields($className)
    {
        $reflectionClass    = new \ReflectionClass($className);
        $translatableFields = array();

        do {
            $translatableFields = array_merge(
                $translatableFields,
                $this->getTranslatableFieldsFromClass($reflectionClass)
            );
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return $translatableFields;
    }

    /**
     * Returns an array containing all attributes from the given entity
     * for which a Gedmo\Translatable annotation is set
     *
     * If the given class name is not an entity, returns an empty array
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @return array
     */
    private function getTranslatableFieldsFromClass(\ReflectionClass $reflectionClass)
    {
        $translatableFields = array();

        if ($this->annotationReader->getClassAnnotation($reflectionClass, 'Doctrine\\ORM\\Mapping\\Entity')) {
            $reflectionProperties = $reflectionClass->getProperties();
            foreach ($reflectionProperties as $reflectionProperty) {
                $propertyAnnotation = $this->annotationReader->getPropertyAnnotation(
                    $reflectionProperty,
                    'Gedmo\Mapping\Annotation\Translatable'
                );

                if ($propertyAnnotation) {
                    $mappingAnnotation                                  = $this->annotationReader->getPropertyAnnotation(
                        $reflectionProperty,
                        'Doctrine\ORM\Mapping\Column'
                    );
                    $translatableFields[$reflectionProperty->getName()] = $mappingAnnotation->type;
                }
            }
        }

        return $translatableFields;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return bool|null|object
     */
    public function isPersonnalTranslationRecursive(\ReflectionClass $class)
    {
        $annotationReader = $this->annotationReader;
        if ($translationAnnotation = $annotationReader->getClassAnnotation(
            $class,
            'Gedmo\\Mapping\\Annotation\\TranslationEntity'
        )
        ) {
            return $translationAnnotation;
        }

        if ($parentClass = $class->getParentClass()) {
            return $this->isPersonnalTranslationRecursive($parentClass);
        }

        return false;
    }

    /**
     * @return \Gedmo\Translatable\TranslatableListener
     */
    protected function getTranslatableListener()
    {
        foreach ($this->doctrine->getManager()->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof TranslatableListener) {
                    return $this->listener = $listener;
                }
            }
        }
    }
}
