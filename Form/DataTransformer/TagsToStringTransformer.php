<?php

namespace Bigfoot\Bundle\CoreBundle\Form\DataTransformer;

use Bigfoot\Bundle\CoreBundle\Entity\Tag;
use Bigfoot\Bundle\CoreBundle\Entity\TagCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;

class TagsToStringTransformer implements DataTransformerInterface
{
    /**
     * Separator used in the string format
     */
    const SEPARATOR = ',';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($tags)
    {
        if (null === $tags or !$tags instanceof PersistentCollection) {
            return '';
        }

        $arrayTags = array();
        foreach ($tags as $tag) {
            $arrayTags[] = $tag->getName();
        }

        return implode(self::SEPARATOR, $arrayTags);
    }

    public function reverseTransform($string)
    {
        $arrayTags = explode(self::SEPARATOR, $string);
        $tags = new ArrayCollection();
        $defaultCategory = $this->getDefaultCategory();

        $em = $this->entityManager;
        $tagRepo = $em->getRepository('BigfootCoreBundle:Tag');
        foreach ($arrayTags as $tag) {
            if ($tag) {
                if (!$tagEntity = $tagRepo->findOneByName($tag)) {
                    $tagEntity = new Tag();
                    $tagEntity->setName($tag);
                    $tagEntity->setCategory($defaultCategory);
                }

                $tags->add($tagEntity);
            }
        }

        return $tags;
    }

    private function getDefaultCategory()
    {
        $em = $this->entityManager;

        $tagCategoryRepo = $em->getRepository('BigfootCoreBundle:TagCategory');
        if (!$defaultCategory = $tagCategoryRepo->findOneBySlug('_default')) {
            $defaultCategory = new TagCategory();
            $defaultCategory->setName('Default category')->setSlug('_default');
        }

        return $defaultCategory;
    }
}