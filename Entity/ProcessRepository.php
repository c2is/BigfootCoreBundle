<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProcessRepository
 * @package Bigfoot\Bundle\CoreBundle\Entity
 */
class ProcessRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllOngoing()
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :ongoing')
            ->orWhere('e.endedAt > :date')
            ->setParameter(':ongoing', Process::STATUS_ONGOING)
            ->setParameter(':date', new \DateTime('5 minutes ago'))
            ->getQuery()
            ->getResult()
        ;
    }
}
