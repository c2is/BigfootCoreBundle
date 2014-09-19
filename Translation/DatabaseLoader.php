<?php

namespace Bigfoot\Bundle\CoreBundle\Translation;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoader implements LoaderInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);

        $repository = $this->entityManager->getRepository('BigfootCoreBundle:TranslatableLabel');
        $translatableLabels = $repository
            ->createQueryBuilder('t')
            ->andWhere('t.domain = :domain')
            ->setParameter(':domain', $domain)
            ->getQuery()
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            )
            ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->getResult()
        ;

        foreach ($translatableLabels as $translatableLabel) {
            $catalogue->set($translatableLabel->getName(), $translatableLabel->getValue(), $domain);
        }

        return $catalogue;
    }
}
