<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MigrateTranslationCommand extends BaseCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:migrate:translation')
            ->setDescription('Update translation entities from ext_translation table (2.2 to Master)')
            ->addOption('delete', null, InputOption::VALUE_OPTIONAL, 'Wanna delete useless ext_translations?');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress  = $this->getHelper('progress');
        $entities  = $this->getContainer()->getParameter('bigfoot_migrate');
        $transRepo = $this->getContainer()->get('bigfoot_core.translation.repository');

        $output->writeln('<info>Migrating translations...</info>');

        foreach ($entities as $objectClass) {
            $output->writeln('');
            $output->writeln(sprintf('<info>Importing entity: %s</info>', $objectClass));

            $translations = $this->getEntityManager()->getRepository('GedmoTranslatable:Translation')
                ->createQueryBuilder('t')
                ->where('t.objectClass = :objectClass')
                ->setParameter('objectClass', $objectClass)
                ->getQuery()
                ->getArrayResult();

            if ($translations) {
                $progress->start($output, count($translations));

                foreach ($translations as $translation) {
                    $originalElement = $this->getRepository($objectClass)->findOneById($translation['foreignKey']);

                    if ($originalElement) {
                        $transRepo->translate($originalElement, $translation['field'], $translation['locale'], $translation['content']);
                        $this->getEntityManager()->persist($originalElement);
                        $this->getEntityManager()->flush();
                    }

                    $this->getEntityManager()->clear();
                    unset($translationEntity);
                    $progress->advance();
                }

                $progress->finish();
            } else {
                $output->writeln(sprintf('<error>No translation found for entity: %s</error>', $objectClass));
            }

            if ($input->getOption('delete') == true) {
                $this->getEntityManager()->getRepository('GedmoTranslatable:Translation')
                    ->createQueryBuilder('t')
                    ->delete()
                    ->where('t.objectClass = :objectClass')
                    ->setParameter('objectClass', $objectClass)
                    ->getQuery()
                    ->getResult();

                $output->writeln(sprintf('<info>ext_translations of entity: %s have been deleted</info>', $objectClass));
            }
        }
    }
}
