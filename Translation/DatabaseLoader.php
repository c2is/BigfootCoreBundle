<?php

namespace Bigfoot\Bundle\CoreBundle\Translation;

use Doctrine\ORM\EntityManager;
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
        $translatableLabels = $repository->findBy(array(
            'locale' => $locale,
            'domain' => $domain,
        ));

        foreach ($translatableLabels as $translatableLabel) {
            $catalogue->set($translatableLabel->getName(), $translatableLabel->getValue(), $domain);
        }

        return $catalogue;
    }
}
