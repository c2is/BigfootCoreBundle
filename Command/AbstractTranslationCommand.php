<?php

namespace Bigfoot\Bundle\CoreBundle\Command;

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Config\Definition\Exception\Exception;

abstract class AbstractTranslationCommand extends BaseCommand
{
    /**
     * @return SpreadsheetService
     * @throws \Exception
     */
    protected function getSpreadsheetService()
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
    protected function getSpreadsheet(
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
}
