<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Gedmo\Translatable\TranslatableListener;
use Doctrine\Common\Annotations\Reader;

class TranslationRepository extends EntityRepository
{
    protected $em;

    private $listener;

    public function __construct(EntityManager $em, Reader $reader)
    {
        $this->reader = $reader;
        $this->em     = $em;
    }

    public function translate($entity, $field, $locale, $fieldData)
    {
        $listener                          = $this->getTranslatableListener();
        $persistDefaultLocaleTransInEntity = $listener->getPersistDefaultLocaleTranslation();
        $entityClass                       = get_class($entity);
        $reflectionClass                   = new \ReflectionClass($entityClass);
        $entityTranslationClass            = $this->reader->getClassAnnotation($reflectionClass, 'Gedmo\\Mapping\\Annotation\\TranslationEntity')->class;

        if(!$persistDefaultLocaleTransInEntity && $locale === $listener->getDefaultLocale()) {
            $trans = new $entityTranslationClass($locale, $field, $fieldData);

            $listener->setTranslationInDefaultLocale(spl_object_hash($entity), $field, $trans);
        } else {

            $translationClassRepository = $this->em->getRepository($entityTranslationClass);
            $translation = $translationClassRepository->findOneBy(array(
                'locale' => $locale,
                'field'  => $field,
                'object' => $entity,
            ));

            if($translation) {
                $translation->setContent($fieldData);
            } else {
                if($fieldData != null) {
                    $entity->addTranslation(new $entityTranslationClass($locale, $field, $fieldData));
                }
            }
        }
    }


    /**
     * Get the currently used TranslatableListener
     *
     * @throws \Gedmo\Exception\RuntimeException - if listener is not found
     * @return TranslatableListener
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
}
