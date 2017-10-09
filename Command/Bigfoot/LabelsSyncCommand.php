<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelTranslation;
use Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository;
use Bigfoot\Bundle\CoreBundle\Manager\TranslatableLabelManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class LabelsSyncCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:labels:sync')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The label dictionary file or directory', 'app/Resources/translatable_label'),
                new InputOption('overwrite', 'o', InputOption::VALUE_NONE, 'Whether to overwrite the translations value or not (defaults to false)'),
                new InputOption('no-delete', 'o-d', InputOption::VALUE_NONE, 'No delete not found labels')
            ))
            ->setDescription('Synchronizes labels stored in database with those found in the file.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command synchronizes the application translations with the given file.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target    = rtrim($input->getArgument('target'), '/');
        $overwrite = $input->getOption('overwrite');
        $noDelete  = $input->getOption('no-delete');

        if (!is_dir($target) && (!file_exists($target))) {
            throw new \InvalidArgumentException(sprintf('The target "%s" does not exist.', $input->getArgument('target')));
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var TranslatableLabelRepository $repo */
        $repo = $em->getRepository('BigfootCoreBundle:TranslatableLabel');
        $transRepo = $this->getContainer()->get('bigfoot_core.translation.repository');
        $defaultLocale = $this->getContainer()->getParameter('locale');

        /** @var ContextService $context */
        $context = $this->getContainer()->get('bigfoot_context');
        $locales = array_keys($context->getValues('language'));

        $files = array();
        if (is_dir($target)) {
            $files = glob($target.'/*.yml');
        } else {
            $files[] = $target;
        }

        $i = 0;
        $processedLabels = array();
        
        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $content = Yaml::parse(file_get_contents($file));

            if ($content) {
                $nbTranslations = count($content);
                $output->writeln(sprintf(' > <comment>Importing file %s with %s translations</comment>', $fileName, $nbTranslations));
                $progress = new ProgressBar($output, $nbTranslations);

                foreach ($content as $name => $translation) {
                    if (substr_count($fileName, '.') == 1) {
                        $name = $fileName.'.'.$name;
                    }

                    $domain = isset($translation['domain']) && $translation['domain'] ? $translation['domain'] : 'messages';
                    $label = $repo->findOneBy(array('name' => $name, 'domain' => $domain));
                    if (!$label) {
                        $label = new TranslatableLabel();
                        $label->setName($name);
                        $label->setDomain($domain);
                    }

                    if (isset($translation['description'])) {
                        if (is_array($translation['description'])) {
                            foreach ($translation['description'] as $locale => $description) {
                                if ($locale == $defaultLocale) {
                                    $label->setDescription($description);
                                } else {
                                    $label->addTranslation(new TranslatableLabelTranslation($locale, 'description', $description));
                                }
                            }
                        } else {
                            $label->setDescription($translation['description']);
                        }
                    }

                    if (isset($translation['plural'])) {
                        $label->setPlural((boolean) $translation['plural']);
                    }

                    if (isset($translation['multiline'])) {
                        $label->setMultiline((boolean) $translation['multiline']);
                    }

                    if (isset($translation['richtext'])) {
                        $label->setRichtext((boolean) $translation['richtext']);
                    }

                    if (isset($translation['value']) && ($overwrite || !$label->getId())) {
                        if (!is_array($translation['value'])) {
                            $translation['value'] = array($defaultLocale => $translation['value']);
                        }

                        foreach ($locales as $locale) {
                            $value = null;

                            if (isset($translation['value'][$locale])) {
                                $value = $translation['value'][$locale];
                            }

                            if ($locale == $defaultLocale) {
                                $label->setValue($value);
                            } elseif ($label->getId()) {
                                $transRepo->translate($label, 'value', $locale, $value);
                            } else {
                                $label->addTranslation(new TranslatableLabelTranslation($locale, 'value', $value));
                            }
                        }
                    }

                    $em->persist($label);
                    if (0 == $i % 100) {
                        $em->flush();
                        $em->clear();
                    }
                    $processedLabels[] = $name.'-'.$domain;

                    $progress->advance();
                    $i++;
                }

                $progress->finish();
            }
        }
        $em->flush();

        if (!$noDelete) {
            $labels = $repo->findAll();
            /** @var TranslatableLabel $label */
            foreach ($labels as $label) {
                $nameDomain = $label->getName().'-'.$label->getDomain();
                if (!in_array($nameDomain, $processedLabels)) {
                    $em->remove($label);
                }
            }
            $em->flush();
        }

        /** @var TranslatableLabelManager $labelManager */
        $labelManager = $this->getContainer()->get('bigfoot_core.manager.translatable_label');
        $labelManager->clearTranslationCache();
    }
}
