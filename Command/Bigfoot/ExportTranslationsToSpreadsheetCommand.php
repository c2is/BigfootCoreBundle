<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Bigfoot\Bundle\CoreBundle\Command\AbstractTranslationCommand;
use Google\Spreadsheet\Batch\BatchRequest;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\Worksheet;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Exports bigfoot translations to spreadsheet
 */
class ExportTranslationsToSpreadsheetCommand extends AbstractTranslationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:translation:spreadsheet-export')
            ->setDescription('Export bigfoot translation to a spreadsheet')
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
            $directory = $this->getContainer()->getParameter('kernel.root_dir') . '/Resources/translatable_label/';
        }

        $fileSystem = $this->getContainer()->get('filesystem');
        if (!$fileSystem->isAbsolutePath($directory) || !$fileSystem->exists($directory) || !is_dir($directory)) {
            throw new InvalidOptionException(sprintf("Option directory is invalid ('%s' given)", $directory));
        }

        $spreadsheetService = $this->getSpreadsheetService();
        $spreadsheet        = $this->getSpreadsheet($spreadsheetService, $input, $output);

        $finder = new Finder();
        $finder->in($directory)->files()->name('*.yml');

        if ($finder->count() == 0) {
            throw new \Exception(sprintf('No yml files found in %s', $directory));
        }

        $availableLanguages = $this->getContainer()->getParameter('bigfoot_core.languages.front');

        $index = 1;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $output->writeln(sprintf('<info>Handling translations %s [</info><comment>%s/%s</comment><info>]</info>', $file->getFilename(), $index++, $finder->count()));

            $fileName = explode('.', $file->getFilename());
            array_pop($fileName);
            $fileName = implode('.', $fileName);

            $translations = Yaml::parse($file->getContents());

            $worksheet = $this->getWorksheetByName($output, $spreadsheet, $fileName, $availableLanguages);
            $this->proceedWorksheet($output, $worksheet, $translations, $availableLanguages, $fileName);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Spreadsheet $spreadsheet
     * @param $workSheetName
     * @param array $availableLanguages
     * @return Worksheet
     */
    private function getWorksheetByName(
        OutputInterface $output,
        Spreadsheet &$spreadsheet,
        $workSheetName,
        $availableLanguages
    ) {
        /** @var Worksheet $worksheet */
        foreach ($spreadsheet->getWorksheets() as $worksheet) {
            if ($worksheet->getTitle() == $workSheetName) {
                return $worksheet;
            }
        }

        $output->write(sprintf('<comment>Creating new worksheet for %s.yml...</comment>', $workSheetName));

        // this case shall be the very first time we execute the command from an empty spreadsheet
        if (count($spreadsheet->getWorksheets()) == 1 && count($worksheet->getCellFeed()->toArray()) == 0) {
            /** @var Worksheet $worksheet */
            $worksheet = $spreadsheet->getWorksheets()[0];
            $worksheet->update($workSheetName, 2 + count($availableLanguages), 1);
        } else {
            $worksheet = $spreadsheet->addWorksheet($workSheetName, 1, 2 + count($availableLanguages));
        }

        $cellFeed = $worksheet->getCellFeed();

        $i = 1;
        $cellFeed->editCell(1, $i++, "Translation label");
        $cellFeed->editCell(1, $i++, "Translation domain");

        foreach ($availableLanguages as $k => $language) {
            $cellFeed->editCell(1, $i++, $k);
        }
        $output->write('    <info>Created !</info>
');

        return $worksheet;
    }

    /**
     * @param OutputInterface $output
     * @param Worksheet $worksheet
     * @param array $translations
     * @param array $availableLanguages
     * @param string $workSheetName
     */
    private function proceedWorksheet(
        OutputInterface $output,
        Worksheet $worksheet,
        $translations,
        $availableLanguages,
        $workSheetName
    ) {
        $listFeed = $worksheet->getListFeed();

        $cellFeed = $worksheet->getCellFeed();
        $translationsInWorksheetTemp = $cellFeed->toArray();

        $translationsInWorksheet = array();
        foreach ($translationsInWorksheetTemp as $k => $trans) {
            //skip header
            if ($k == 1) {
                continue;
            }
            $translationsInWorksheet[$trans[1]] = $trans[2];
        }

        $translationsToWriteInWorksheet = array_diff_key($translations, $translationsInWorksheet);

        $output->writeln(sprintf('<comment>Writing worksheet %s (</comment><info>%s translations to write</info><comment>)...</comment>', $workSheetName, count($translationsToWriteInWorksheet)));

        if (count($translationsToWriteInWorksheet) > 0) {
            $progressBar = new ProgressBar($output, count($translationsToWriteInWorksheet));
            $progressBar->start();

            foreach ($translationsToWriteInWorksheet as $transLabel => $trans) {
                $row = [];
                $row['translationlabel'] = $transLabel;
                $row['translationdomain'] = $trans['domain'];
                foreach ($availableLanguages as $language) {
                    if (isset($trans['value'][$language['value']])) {
                        $label = $trans['value'][$language['value']];
                        $row[$language['value']] = $label;
                    }
                }
                $listFeed->insert($row);
                $progressBar->advance();
            }
            $progressBar->finish();
            $output->writeln('');
        }
        $output->writeln('');
    }
}
