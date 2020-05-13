<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;

class AdjustAcceptedDateCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:adjust-accepted-date';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Sets the accepted to first accepted dateset per dataset.')
            ->addArgument('csvfile', InputArgument::REQUIRED, 'The CSV files with UDIs.')
        ;
    }

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

     /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $filename = $input->getArgument('csvfile');
        
        $csvData = array();
        
        $file = fopen($filename, 'r');
        while (($data = fgetcsv($file)) !== FALSE) {
          array_push($csvData, array('udi' => $data[0], 'date' => $data[1]));
          //$csvData[] = array('udi' => $data[0], 'date' => $data[1]);
        }
        fclose($file);
        
        // var_dump($csvData);
        
        // return 1;

        $datasets = $this->entityManager
            ->getRepository(Dataset::class)
            ->findAll();

        $datasetCount = 0;

        foreach ($datasets as $dataset) {
            $datasetStatus = $dataset->getDatasetStatus();
            $udi = $dataset->getUdi();
            
            $inCsv = array_search($udi, array_column($csvData, 'udi'));
            
            //$inCsv = in_array($udi, $csvData['udi']);
            
            // if ($inCsv) {
                // continue;
            // }
            
            if ($inCsv) {
                $csvDate = $csvData[$inCsv]['date'];
                
                $timeZone = new \DateTimeZone('America/Chicago');
            
                $originalDate = new \DateTime($csvDate, new \DateTimeZone('UTC'));
                $originalDate->setTimezone($timeZone);
                
                $frmtoriginalDate = $originalDate->format('Y-m-d H:i:s');
            } else {
                $frmtoriginalDate= '';
            }

            //if ($datasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
                $datasetSubmissionHistory = $dataset->getDatasetSubmissionHistory();
                $lastAcceptedDate = $dataset->getAcceptedDate();
                
                if (empty($lastAcceptedDate)) {
                    continue;
                }
                
                $frmtlastAcceptedDate = null;
                if ($lastAcceptedDate instanceof \Datetime) {
                    $frmtlastAcceptedDate = $lastAcceptedDate->format('Y-m-d H:i:s');
                }
                
                $newAcceptedDate = $lastAcceptedDate;

                foreach ($datasetSubmissionHistory as $datasetSubmission) {
                    $lastDatasetStatus = $datasetSubmission->getDatasetStatus();
                    if ($lastDatasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
                        $newAcceptedDate = $datasetSubmission->getModificationTimeStamp(true);
                    }
                }

                $frmtnewAcceptedDate = null;
                if ($newAcceptedDate instanceof \Datetime) {
                    $frmtnewAcceptedDate = $newAcceptedDate->format('Y-m-d H:i:s');
                }

                //if ($lastAcceptedDate <> $newAcceptedDate) {
                    
                    echo "$udi,$frmtlastAcceptedDate,$frmtnewAcceptedDate,$frmtoriginalDate \n";
                    //echo "$udi is different\n";

                    // $dataset->setAcceptedDate($newAcceptedDate);

                    // $this->entityManager->persist($dataset);
                    // $this->entityManager->flush($dataset);

                    $datasetCount++;
                //}
            //}
        }

        $io->note("Proccesed $datasetCount datasets");

        $io->success('Done, all dates updated!');

        return 0;
    }
}
