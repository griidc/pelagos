<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\ISOMetadataExtractorUtil;
use Pelagos\Entity\Person;

/**
 * This class generates two reports.
 *
 * The first report makes a list of the Point Of Contact (hereafter POC) instances that contain an
 * email address that is invalid.
 *
 * The second report lists any POC in which there is an invalid email address along with the invalid email.
 */
class MissingPrimaryContactsCommand extends ContainerAwareCommand
{

    /**
     * A name of the file that will store the results of this report program.
     *
     * It is initialized to a default value.
     *
     * @var string
     */
    protected $outputFileName   = '/home//pelagos/scratch/missing-primary-contacts.txt';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface fileOutput
     */
    protected $fileOutput = null;

    /**
     * The Doctrine entity manager - ORM critter.
     *
     * @var EntityManager entityManager
     */
    protected $entityManager;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('reports:missing-primary-contacts')
            ->setDescription('Description: reports:missing-primary-contants-command')
            ->addArgument('OutputFileNmae', InputArgument::REQUIRED, 'What is the output file path and name?');
    }

    /**
     * Prepare the output file for writting.
     *
     * @param OutputInterface $output An class that handles output files.
     *
     * @throws \Exception When no datasets are found.
     * @return boolean
     */
    private function openIO(OutputInterface $output)
    {
        if ($this->fileOutput == null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
            $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        // $this->datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findAll();
        $this->datasets = array();
        $this->datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')
            ->findBy($this->datasets, array('udi' => 'ASC'));

        if (count($this->datasets) == 0) {
            throw new \Exception('Could not find all the datasets');
        }
        return true;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFileName = $input->getArgument('OutputFileNmae');
        self::openIO($output);
        $this->missingPrimaryContactReport();

        $this->allMissingContactsReport();
        return 0;
    }

    /**
     * Find and report all the Datasets with invalid email addresses.
     *
     * That is ones in which the first email address in the first POC is not found in an existing Person instance.
     *
     * @return integer
     */
    protected function missingPrimaryContactReport()
    {
        $acceptedMetadataStatusCount = 0;
        $xmlContactsWithoutMatchingPerson = 0;
        $blankXmlContactsEmail = 0;
        $this->fileOutput->writeln(
            '\nDatasets in which the 1st email address of the 1st ' .
            'POC does not match a person in the system'
        );
        $this->fileOutput->writeln('\nDataset ID,Metadata XML email address: ');
        $badCount = 0;
        foreach ($this->datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $status = $dataset->getDatasetStatus();
            $this->datasetsubmission = $dataset->getDatasetSubmission();
            $metadata = $dataset->getMetadata();
            if ($status == 'Accepted') {
                $acceptedMetadataStatusCount ++;
                if ($metadata) {
                    $simpleXml = $metadata->getXml();
                    if ($simpleXml->asXml()) {
                        if ($this->datasetsubmission) {
                            $firstEmailAddressFrom1stPOC
                                = ISOMetadataExtractorUtil::get1stEmailAddressesFrom1stPointOfContact(
                                    $simpleXml,
                                    $this->datasetsubmission,
                                    $this->entityManager
                                );
                            if ($firstEmailAddressFrom1stPOC) {
                                if (strlen($firstEmailAddressFrom1stPOC) == 0 || $firstEmailAddressFrom1stPOC == '') {
                                    $this->fileOutputArray[] = '** Blank **';
                                    $blankXmlContactsEmail ++;
                                } else {
                                    $person = $this->getPersonEmailContacts($firstEmailAddressFrom1stPOC);
                                    if ($person == null) {
                                        $xmlContactsWithoutMatchingPerson ++;
                                        $this->fileOutputArray[] = $firstEmailAddressFrom1stPOC;
                                    }
                                }
                            }
                        }
                    } else {
                        $this->fileOutput->writeln('NO DatasetSubmission');
                    }
                }
            }
            $this->printResults();
        }

        $this->fileOutput->writeln('Datasetsets found: ' . count($this->datasets));
        $this->fileOutput->writeln('accepted metadata: ' . $acceptedMetadataStatusCount);
        $this->fileOutput->writeln(
            '\t' . 'Accepted metadata emails that do not match a Person: ' .
            $xmlContactsWithoutMatchingPerson
        );
        $this->fileOutput->writeln('\t' . 'Blank or empty email addresses: ' . $blankXmlContactsEmail);

        return 0;
    }

    /**
     * Find all Datasets for which there is a bad email address.
     *
     * All POCs that have Persons that have bad email addresses.
     *
     * @return integer
     */
    protected function allMissingContactsReport()
    {
        $approvedMetadataStatusCount = 0;
        $acceptedMetadataStatusCount = 0;
        $backToSubmitterMetadataStatusCount = 0;
        $otherMetadataStatusCount = 0;
        $noneMetadataStatusCount = 0;
        $inReviewMetadataStatusCount = 0;
        $submittedMetadataStatusCount = 0;
        $xmlContactsWithoutMatchingPerson = 0;
        $blankXmlContactsEmail = 0;
        $this->fileOutput->writeln(
            '\nDatasets in which the any email address of the any ' .
            'POC does not match a person in the system'
        );
        $this->fileOutput->writeln('\nDataset ID,Metadata XML email address: ');
        $badCount = 0;
        foreach ($this->datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $status = $dataset->getDatasetStatus();
            $this->datasetsubmission = $dataset->getDatasetSubmission();
            $metadata = $dataset->getMetadata();
            if ($status == 'Approved') {
                $approvedMetadataStatusCount ++;
            } elseif ($status == 'Accepted') {
                $acceptedMetadataStatusCount ++;
                if ($metadata) {
                    $simpleXml = $metadata->getXml();
                    if ($simpleXml->asXml()) {
                        if ($this->datasetsubmission) {
                            $allEmailAddressesForAllPOCs
                                = ISOMetadataExtractorUtil::getAllEmailAddressesForAllPointsOfContact(
                                    $simpleXml,
                                    $this->datasetsubmission,
                                    $this->entityManager
                                );
                            if (count($allEmailAddressesForAllPOCs) >= 0) {
                                foreach ($allEmailAddressesForAllPOCs as $emailAddr) {
                                    if (strlen($emailAddr) == 0 || $emailAddr == '') {
                                        $this->fileOutputArray[] = '** Blank **';
                                        $blankXmlContactsEmail ++;
                                    } else {
                                        $person = $this->getPersonEmailContacts($emailAddr);
                                        if ($person == null) {
                                            $xmlContactsWithoutMatchingPerson ++;
                                            $this->fileOutputArray[] = $emailAddr;
                                        }
                                    }
                                }
                            } else {
                                $this->fileOutput->writeln('No email addresses returned in XML metadata');
                            }
                        } else {
                            $this->fileOutput->writeln('NO DatasetSubmission');
                        }
                    }
                }
            } elseif ($status == 'BackToSubmitter') {
                $backToSubmitterMetadataStatusCount ++;
            } elseif ($status == 'None') {
                $noneMetadataStatusCount ++;
            } elseif ($status == 'InReview') {
                $inReviewMetadataStatusCount ++;
            } elseif ($status == 'Submitted') {
                $submittedMetadataStatusCount ++;
            } else {
                $otherMetadataStatusCount ++;
            }

            $this->printResults();

        }

        $this->fileOutput->writeln('Datasetsets found: ' . count($this->datasets));
        $this->fileOutput->writeln('approved metadata: ' . $approvedMetadataStatusCount);
        $this->fileOutput->writeln('accepted metadata: ' . $acceptedMetadataStatusCount);
        $this->fileOutput->writeln(
            '\t' . 'Accepted metadata emails that do not match a Person: ' .
            $xmlContactsWithoutMatchingPerson
        );
        $this->fileOutput->writeln('\t' . 'Blank or empty email addresses found: ' . $blankXmlContactsEmail);
        $this->fileOutput->writeln('back to submitter metadata: ' . $backToSubmitterMetadataStatusCount);
        $this->fileOutput->writeln('None metadata: ' . $noneMetadataStatusCount);
        $this->fileOutput->writeln('InReview metadata ' . $inReviewMetadataStatusCount);
        $this->fileOutput->writeln('Submitted metadata ' . $submittedMetadataStatusCount);
        $this->fileOutput->writeln('other metadata: ' . $otherMetadataStatusCount);

        return 0;
    }

    /**
     * Send the results to the printer.
     *
     * This also adds the comma delimiter.
     *
     * @return integer
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
        return 0;
    }

    /**
     * Get an array of Person instances that have the target email address.
     *
     * @param string $emailAddress An email address in the typical form name@domain.host.
     *
     * @return null
     */
    protected function getPersonEmailContacts($emailAddress)
    {
        $personArray = $this->entityManager->getRepository(Person::class)
            ->findBy(array('emailAddress' => strtolower($emailAddress)));
        $person = null;
        if (count($personArray) > 0) {
            $person = $personArray[0];
        }
        return $person;
    }
}
