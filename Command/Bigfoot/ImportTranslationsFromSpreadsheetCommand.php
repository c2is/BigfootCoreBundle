<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Bigfoot\Bundle\CoreBundle\Command\AbstractTranslationCommand;
use Google\Spreadsheet\Worksheet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Imports bigfoot translation from spreadsheet
 */
class ImportTranslationsFromSpreadsheetCommand extends AbstractTranslationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:translation:spreadsheet-import')
            ->setDescription('Imports bigfoot translation from spreadsheet')
            ->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, "Absolute path to translations' directory");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === ($directory = $input->getOption('directory'))) {
            $directory = $this->getContainer()->getParameter('kernel.root_dir') . '/Resources/translatable_label_test/';
        }

        $fileSystem = $this->getContainer()->get('filesystem');
        if (!$fileSystem->isAbsolutePath($directory) || !$fileSystem->exists($directory) || !is_dir($directory)) {
            throw new InvalidOptionException(sprintf("Option directory is invalid ('%s' given)", $directory));
        }

        $spreadsheetService = $this->getSpreadsheetService();
        $spreadsheet        = $this->getSpreadsheet($spreadsheetService, $input, $output);

        $availableLanguages = array_keys($this->getContainer()->getParameter('bigfoot_core.languages.front'));

        /** @var Worksheet $worksheet */
        foreach ($spreadsheet->getWorksheets() as $worksheet) {
            $file         = $directory . '/' . $worksheet->getTitle() . '.yml';
            $translations = array();

            $cellFeed                = $worksheet->getCellFeed();
            $translationsInWorksheet = $cellFeed->toArray();
            foreach ($translationsInWorksheet as $k => $trans) {
                //skip header
                if ($k == 1) {
                    continue;
                }

                $translationKey = $trans[1];
                foreach ($trans as $colNumber => $cellContent) {
                    if ($colNumber == 1) {
                        $translationKey = $cellContent;
                    } elseif ($colNumber == 2) {
                        $translations[$translationKey]['domain'] = $cellContent;
                    } else {
                        echo $cellContent.'
                        ';
                        $translations[$translationKey]['value'][$availableLanguages[$colNumber - 3]] = $cellContent;
                    }
                }
            }

            //$fileSystem->touch($file);
            if (count($translations)) {
                $yaml = Yaml::dump($translations);
                file_put_contents($file, $yaml);
            }
        }
    }
}
