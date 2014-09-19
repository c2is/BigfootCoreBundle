<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * TranslatableLabelRepository
 */
class TranslatableLabelRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $query
     * @param $value
     * @return QueryBuilder
     */
    public function addCategoryFilter(QueryBuilder $query, $value)
    {
        return $query->andWhere('e.name LIKE :category')->setParameter(':category', $value.'%');
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $categories = array();

        $results = $this->findAll();
        /** @var TranslatableLabel $result */
        foreach ($results as $result) {
            if (!in_array($category = $result->getCategory(), $categories)) {
                $categories[] = $category;
            }
        }

        asort($categories);

        return array_combine($categories, $categories);
    }
}
