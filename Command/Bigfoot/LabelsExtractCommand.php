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
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class LabelsExtractCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:labels:extract')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'app/Resources/translatable_label'),
                new InputOption('overwrite', 'o', InputOption::VALUE_NONE, 'Whether to overwrite the translations value or not (defaults to false)'),
                new InputOption('auto-plural', 'p', InputOption::VALUE_NONE, 'Sets plural to true if a pipe character is found in the label value'),
                new InputOption('auto-multiline', 'm', InputOption::VALUE_NONE, 'Sets multiline to true if a new line character is found in the label value')
            ))
            ->setDescription('Synchronizes labels stored in files with those found in database.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command synchronizes the application translation files with the database data.
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
        $target = rtrim($input->getArgument('target'), '/');

        if (!is_dir($target)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        $overwrite = $input->getOption('overwrite');
        $autoPlural = $input->getOption('auto-plural');
        $autoMultiline = $input->getOption('auto-multiline');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var TranslatableLabelRepository $repo */
        $repo = $em->getRepository('BigfootCoreBundle:TranslatableLabel');
        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $transRepo = $this->getContainer()->get('bigfoot_core.translation.repository');
        /** @var ContextService $context */
        $context = $this->getContainer()->get('bigfoot_context');
        $locales = array_keys($context->getValues('language'));

        $categories = array();
        $labels = $repo->findAll();
        $nbLabels = count($labels);
        $progress->start($output, $nbLabels);
        $output->writeln(sprintf(' > <info>Extracting %s translations from database</info>', $nbLabels));

        /** @var TranslatableLabel $label */
        foreach ($labels as $label) {
            $category = $label->getCategory();
            if (!$category || false !== strpos($category, ' ') || false === strpos($category, '.')) {
                $category = 'default';
            }

            if (!isset($categories[$category])) {
                $categories[$category] = array();
            }

            $categoryPrefix = $category.'.';
            $labelName = $label->getName();
            if (strpos($labelName, $categoryPrefix) === 0) {
                $labelName = substr($label->getName(), strlen($categoryPrefix));
            }

            $labelArray = array();
            $labelArray['domain'] = $label->getDomain();

            $descriptions = array();
            $values = array();
            $hasPipe = false;
            $hasNewLine = false;
            foreach ($locales as $locale) {
                $label->setTranslatableLocale($locale);
                $em->refresh($label);

                if ($label->getDescription()) {
                    $descriptions[$locale] = $label->getDescription();
                }
                if ($label->getValue()) {
                    $values[$locale] = $label->getValue();
                }

                if (false !== strpos($label->getValue(), '|')) {
                    $hasPipe = true;
                }
                if (false !== strpos($label->getValue(), "\n")) {
                    $hasNewLine = true;
                }
            }
            if ($descriptions) {
                $labelArray['description'] = $descriptions;
            }
            if ($values) {
                $labelArray['value'] = $values;
            } else {
                $labelArray['value'] = '';
            }
            if ($label->isPlural() || ($autoPlural && $hasPipe)) {
                $labelArray['plural'] = true;
            }
            if ($label->isMultiline() || ($autoMultiline && $hasNewLine)) {
                $labelArray['multiline'] = true;
            }

            $categories[$category][$labelName] = $labelArray;

            $progress->advance();
        }
        $progress->finish();

        $nbFiles = count($categories);
        $progress->start($output, $nbFiles);
        $output->writeln(sprintf(' > <info>Writing %s translation files</info>', $nbFiles));
        foreach ($categories as $categoryName => $categoryContent) {
            $fileName = sprintf('%s.yml', $categoryName);
            $file = sprintf('%s/%s', rtrim($target, '/'), $fileName);
            if (!$overwrite && file_exists($file)) {
                $output->writeln(sprintf(' > <comment>File %s already exists and was not overwritten (to overwrite, call the command with the --overwrite or -o option)</comment>', $file));
            } else {
                file_put_contents($file, Yaml::dump($categoryContent));
            }
            $progress->advance();
        }
        $progress->finish();
    }
}
