<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Google\Spreadsheet\Batch\BatchRequest;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use Google\Spreadsheet\Worksheet;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Runs bigfoot:theme:install and assets:install
 */
class ExportTranslationsToSpreadsheetCommand extends BaseCommand
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

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileName = explode('.', $file->getFilename());
            array_pop($fileName);
            $fileName = implode('.', $fileName);

            $translations = Yaml::parse($file->getContents());

            $worksheet = $this->getWorksheetByName($output, $spreadsheet, $fileName, $availableLanguages);
            $this->proceedWorksheet($output, $worksheet, $translations, $availableLanguages, $fileName);
        }
    }

    /**
     * @return SpreadsheetService
     * @throws \Exception
     */
    private function getSpreadsheetService()
    {
        $oAuth          = $this->getContainer()->get('google_drive.oauth');
        $token          = $oAuth->authenticate();
        $serviceRequest = new DefaultServiceRequest($token);
        ServiceRequestFactory::setInstance($serviceRequest);
        $spreadsheetService = new SpreadsheetService();

        return $spreadsheetService;
    }

    /**
     * @param SpreadsheetService $spreadsheetService
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Spreadsheet
     */
    private function getSpreadsheet(
        SpreadsheetService $spreadsheetService,
        InputInterface $input,
        OutputInterface $output
    ) {
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
        if (false === $this->getContainer()->hasParameter('bigfoot_core.translation.spreadsheet')) {
            /** @var Spreadsheet $spreadsheet */
            foreach ($spreadsheetFeed as $index => $listedSpreadsheet) {
                $output->writeln('    [<info>' . $index . '</info>] ' . $listedSpreadsheet->getTitle());
            }

            $spreadsheetIndex = $this->getHelper('question')->ask(
                $input,
                $output,
                new Question('Spreadsheet to chose :')
            );
            $output->writeln(sprintf('<info>spreadsheet chosen :</info> %s', $spreadsheetFeed[$spreadsheetIndex]->getTitle()));
            $output->writeln(sprintf("<comment>You can define 'bigfoot_core.translation.spreadsheet' with '%s' to skip this step</comment>", $spreadsheetFeed[$spreadsheetIndex]->getId()));

            $spreadsheet = $spreadsheetFeed[$spreadsheetIndex];
        } else {
            $spreadsheetId = $this->getContainer()->getParameter('bigfoot_core.translation.spreadsheet');
            $spreadsheet   = null;
            /** @var Spreadsheet $spreadsheet */
            foreach ($spreadsheetFeed as $index => $listedSpreadsheet) {
                if ($listedSpreadsheet->getId() == $spreadsheetId) {
                    $spreadsheet = $listedSpreadsheet;
                }
            }

            if (null === $spreadsheet) {
                throw new Exception("Invalid argument under bigfoot_core.translation.spreadsheet key");
            }
        }

        return $spreadsheet;
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
        } else {
            $worksheet = $spreadsheet->addWorksheet($workSheetName, 1, 2 + count($availableLanguages));
        }

        $cellFeed = $worksheet->getCellFeed();

        $i = 1;
        $cellFeed->editCell(1, $i++, "Translation label");
        $cellFeed->editCell(1, $i++, "Translation domain");

        foreach ($availableLanguages as $language) {
            $cellFeed->editCell(1, $i++, "Label in" . $language['label']);
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
     */
    private function proceedWorksheet(OutputInterface $output, Worksheet $worksheet, $translations, $availableLanguages, $workSheetName)
    {
        $cellFeed                    = $worksheet->getCellFeed();
        $translationsInWorksheetTemp = $cellFeed->toArray();
        $rowCount                    = count($translationsInWorksheetTemp);

        $translationsInWorksheet = array();
        foreach ($translationsInWorksheetTemp as $k => $trans) {
            //skip header
            if ($k == 1) {
                continue;
            }
            $translationsInWorksheet[$trans[1]] = $trans[2];
        }

        $translationsToWriteInWorksheet = array_diff_key($translations, $translationsInWorksheet);
        $batchRequest                   = new BatchRequest();

        $worksheet->update($workSheetName, 2 + count($availableLanguages), $rowCount + count($translationsToWriteInWorksheet));

        $progressBar = new ProgressBar($output, count($translationsToWriteInWorksheet));
        $progressBar->start();
        $progressBar->setFormat('very_verbose');

        foreach ($translationsToWriteInWorksheet as $transLabel => $trans) {
            $batchRequest->addEntry($cellFeed->createInsertionCell(++$rowCount, 1, $transLabel));
            $batchRequest->addEntry($cellFeed->createInsertionCell($rowCount, 2, $trans['domain']));
            foreach ($availableLanguages as $language) {
                if (isset($trans['value'][$language['value']])) {
                    $index = array_flip(array_keys($availableLanguages))[$language['value']];
                    $label = $trans['value'][$language['value']];
                    $label = str_replace('<', '&lt;', $label);
                    $label = str_replace('>', '&gt;', $label);
                    $batchRequest->addEntry($cellFeed->createInsertionCell($rowCount, 3 + $index, $label));
                }
            }
            $progressBar->advance();
        }
        $cellFeed->insertBatch($batchRequest);
        $progressBar->finish();
        $output->writeln('');
    }
}
