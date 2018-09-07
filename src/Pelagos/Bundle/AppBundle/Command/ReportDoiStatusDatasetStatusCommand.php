<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Exception;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DOI;
use Pelagos\Util\DOIutil;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

use Doctrine\ORM\EntityManager;

/**
 * This Symfony Command generates a report for DOI migration.
 *
 * @see ContainerAwareCommand
 */
class ReportDoiStatusDatasetStatusCommand extends ContainerAwareCommand
{
    /**
     * A name of the file that will store the results of this report program.
     *
     * It is initialized to a default value.
     *
     * @var string
     */
    protected $outputFileName = '';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface fileOutput
     */
    protected $fileOutput = null;

    /**
     * The file output array which stores the data.
     *
     * @var array
     */
    protected $fileOutputArray;

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('reports:doi-status-datasets')
            ->setDescription('Report of udi(s) with doi status, dataset status')
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output file path and name?');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws Exception Exception thrown when openIO function fails to generate report.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFileName = $input->getArgument('outputFileName');
        try {
            $datasets = self::openIO($output);
            $this->createReportForDoiMigration($datasets);
        } catch (Exception $e) {
            throw new Exception('Unable to generate report ' . $e->getMessage());
        }
    }

    /**
     * Prepare the output file for writting.
     *
     * @param OutputInterface $output An class that handles output files.
     *
     * @return array
     */
    protected function openIO(OutputInterface $output)
    {
        $entityManager = null;
        if ($this->fileOutput === null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
            $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        $datasets = array();
        $datasets = $entityManager->getRepository(Dataset::class)
            ->findBy($datasets, array('udi' => 'ASC'));

        return $datasets;
    }

    /**
     * Generate report for doi migration.
     *
     * @param array $datasets Collection of datasets.
     *
     * @return void
     */
    private function createReportForDoiMigration(array $datasets)
    {
        $headers = array(
            'udi',
            'dataset_status',
            'doi_status',
            'doi_id'
        );

        $this->fileOutput->writeln(implode(',', $headers));

        foreach ($datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $this->fileOutputArray[] = $dataset->getMetadataStatus();
            $doi = $this->getDoiStatus($dataset);
            if (!empty($doi)) {
                $this->fileOutputArray[] = $doi['status'];
                $this->fileOutputArray[] = $doi['id'];
            }
            $this->printResults();
        }
    }

    /**
     * Gets the DOI status from EZ API.
     *
     * @param Dataset $dataset The dataset instance.
     *
     * @throws Exception Exception thrown when doi metadata method fails.
     *
     * @return array
     */
    private function getDoiStatus(Dataset $dataset)
    {
        $doiArray = array();

        if ($dataset->getDoi() instanceof DOI) {
            try {
                $doiUtil = new DOIutil();
                $doiMetadata = $doiUtil->getDOIMetadata($dataset->getDoi()->getDoi());
                $doiArray['status'] = $doiMetadata['_status'];
                $doiArray['id'] = $dataset->getDoi()->getDoi();

            } catch (Exception $e) {
                throw new Exception('Unable to get DOI metadata ' . $e->getMessage());
            }
        }

        return $doiArray;
    }

    /**
     * Send the results to the printer.
     *
     * This also adds the comma delimiter.
     *
     * @return void
     */
    private function printResults()
    {
        if (count($this->fileOutputArray) >= 2) {
            $stringBuffer = '';
            for ($n = 0; $n < count($this->fileOutputArray); $n++) {
                if ($n >= 1) {
                    $stringBuffer .= ',';
                }
                $stringBuffer .= $this->fileOutputArray[$n];
            }
            $this->fileOutput->writeln($stringBuffer);
        }
    }
}
