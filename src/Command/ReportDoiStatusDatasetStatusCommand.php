<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

use App\Entity\Dataset;
use App\Entity\DOI;
use App\Util\DOIutil;

/**
 * This Symfony Command generates a report for DOI migration.
 *
 * @see Command
 */
class ReportDoiStatusDatasetStatusCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'reports:doi-status-datasets';

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
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * DOI Utility class.
     *
     * @var DOIutil
     */
    protected $doiUtil;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param DOIutil                $doiUtil       Doi utility class instance.
     */
    public function __construct(EntityManagerInterface $entityManager, DOIutil $doiUtil)
    {
        $this->entityManager = $entityManager;
        $this->doiUtil = $doiUtil;
        parent::__construct();
    }

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Report of udi(s) with doi status, dataset status')
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output file path and name?');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception Exception thrown when openIO function fails to generate report.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFileName = $input->getArgument('outputFileName');
        try {
            $datasets = self::openIO($output);
            $this->createReportForDoiMigration($datasets);
        } catch (\Exception $e) {
            throw new \Exception('Unable to generate report ' . $e->getMessage());
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
        if ($this->fileOutput === null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        $datasets = array();
        $datasets = $this->entityManager->getRepository(Dataset::class)
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
            $this->fileOutputArray[] = $dataset->getDatasetStatus();
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
     * @throws \Exception Exception thrown when doi metadata method fails.
     *
     * @return array
     */
    private function getDoiStatus(Dataset $dataset)
    {
        $doiArray = array();

        if ($dataset->getDoi() instanceof DOI) {
            try {
                $doiMetadata = $this->doiUtil->getDOIMetadata($dataset->getDoi()->getDoi());
                $doiArray['status'] = $doiMetadata['_status'];
                $doiArray['id'] = $dataset->getDoi()->getDoi();
            } catch (\Exception $e) {
                throw new \Exception('Unable to get DOI metadata ' . $e->getMessage());
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
