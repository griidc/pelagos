<?php
/**
 * Created by PhpStorm.
 * User: jvh
 * Date: 5/25/17
 * Time: 2:06 PM
 */

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

class MissingPrimaryContactsCommand extends ContainerAwareCommand
{

    protected $outputFileName   = '/home//pelagos/scratch/missing-primary-contacts.txt';
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $fileOutput = null;

    /**
     * The Doctrine entity manager - ORM critter
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
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */

    private function openIO(OutputInterface $output) {
        if($this->fileOutput == null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
            $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        // $this->datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findAll();
        $this->datasets = array();
        $this->datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findBy($this->datasets,array('udi' => 'ASC'));

        if (count($this->datasets) == 0) {
            throw new \Exception('Could not find all the datasets');
        }

    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFileName =$input->getArgument('OutputFileNmae');
        self::openIO($output);
        $this->reportOne($input, $output);

        $this->reportTwo($input, $output);
    }

    protected function reportOne(InputInterface $input, OutputInterface $output) {


        $acceptedMetadataStatusCount = 0;
        $hasMetadataCount = 0;
        $hasMetadataXmlCount = 0;
        $xmlContactsWithoutMatchingPerson = 0;
        $blankXmlContactsEmail = 0;
        $this->fileOutput->writeln("\nDatasets in which the first metadata xml email address does not match a person in the system");
        $this->fileOutput->writeln("\nDataset ID,Metadata XML email address: ");
        $badCount = 0;
        foreach ( $this->datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $status = $dataset->getMetadataStatus();
            $this->datasetsubmission = $dataset->getDatasetSubmission();
            $metadata = $dataset->getMetadata();
            if ($status == 'Accepted') {
                $acceptedMetadataStatusCount += 1;
                if ($metadata) {
                    $hasMetadataCount += 1;
                    $simpleXml = $metadata->getXml();
                    if ($simpleXml->asXml()) {
                        $hasMetadataXmlCount += 1;
                        if ($this->datasetsubmission) {
                            $allEmailAddressesForAllPOCs = ISOMetadataExtractorUtil::getAllEmailAddressesForAllPointsOfContact(
                                $simpleXml,
                                $this->datasetsubmission,
                                $this->entityManager);
                            if (count($allEmailAddressesForAllPOCs) >= 0) {
                                $emailAddr = $allEmailAddressesForAllPOCs[0];  //  get the first email address
                                if (strlen($emailAddr) == 0 || $emailAddr == '') {
                                    $this->fileOutputArray[] = "** Blank **";
                                    $blankXmlContactsEmail += 1;
                                } else {
                                    $person = $this->getPersonEmailContacts($emailAddr);
                                    if ($person == null) {
                                        $xmlContactsWithoutMatchingPerson += 1;
                                        $this->fileOutputArray[] = $emailAddr;
                                    }
                                }
                            }
                        }
                    } else {
                        $this->fileOutput->writeln("NO DatasetSubmission");
                    }
                }
            }
            $this->printResults($output);
        }

        $this->fileOutput->writeln('Datasetsets found: ' . count($this->datasets));
        $this->fileOutput->writeln('accepted metadata: ' . $acceptedMetadataStatusCount);
        $this->fileOutput->writeln("\t" . 'Accepted metadata emails that do not match a Person: ' . $xmlContactsWithoutMatchingPerson);
        $this->fileOutput->writeln("\t" . 'Blank or empty email addresses: ' . $blankXmlContactsEmail);

        return 0;
    }


    protected function reportTwo(InputInterface $input, OutputInterface $output) {


        $approvedMetadataStatusCount = 0;
        $acceptedMetadataStatusCount = 0;
        $backToSubmitterMetadataStatusCount = 0;
        $otherMetadataStatusCount = 0;
        $noneMetadataStatusCount = 0;
        $InReviewMetadataStatusCount = 0;
        $SubmittedMetadataStatusCount = 0;
        $hasMetadataCount = 0;
        $hasMetadataXmlCount = 0;
        $xmlContactsWithoutMatchingPerson = 0;
        $blankXmlContactsEmail = 0;
        $this->fileOutput->writeln("\nDatasets in which metadata xml email address does not match a person in the system");
        $this->fileOutput->writeln("\nDataset ID,Metadata XML email address: ");
        $badCount = 0;
        foreach ( $this->datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $status = $dataset->getMetadataStatus();
            $this->datasetsubmission = $dataset->getDatasetSubmission();
            $metadata = $dataset->getMetadata();
            if( $status == 'Approved' ) {
                $approvedMetadataStatusCount += 1;
            } elseif ($status == 'Accepted' ) {
                $acceptedMetadataStatusCount += 1;
                if($metadata) {
                    $hasMetadataCount += 1;
                    $simpleXml = $metadata->getXml();
                    if ($simpleXml->asXml()) {
                        $hasMetadataXmlCount += 1;
                        if ($this->datasetsubmission ) {
                            $allEmailAddressesForAllPOCs = ISOMetadataExtractorUtil::getAllEmailAddressesForAllPointsOfContact(
                                $simpleXml,
                                $this->datasetsubmission,
                                $this->entityManager);
                            if(count($allEmailAddressesForAllPOCs) >= 0) {
                                foreach ($allEmailAddressesForAllPOCs as $emailAddr) {
                                    if(strlen($emailAddr) == 0 || $emailAddr  == '') {
                                        $this->fileOutputArray[] = "** Blank **" ;
                                        $blankXmlContactsEmail += 1;
                                    } else {
                                        $person = $this->getPersonEmailContacts($emailAddr);
                                        if ($person == null) {
                                            $xmlContactsWithoutMatchingPerson += 1;
                                            $this->fileOutputArray[] = $emailAddr;
                                        }
                                    }
                                }
                            } else {
                                $this->fileOutput->writeln("No email addresses returned in XML metadata");
                            }
                        } else {
                            $this->fileOutput->writeln("NO DatasetSubmission");
                        }
                    }
                }
            } elseif ( $status == 'BackToSubmitter') {
                $backToSubmitterMetadataStatusCount += 1;
            } elseif ( $status == 'None') {
                $noneMetadataStatusCount += 1;
            } elseif ( $status == 'InReview') {
                $InReviewMetadataStatusCount += 1;
            }  elseif ( $status == 'Submitted') {
                $SubmittedMetadataStatusCount += 1;
            } else {
                $otherMetadataStatusCount += 1;
            }

            $this->printResults($output);

        }

        $this->fileOutput->writeln('Datasetsets found: ' . count($this->datasets));
        $this->fileOutput->writeln('approved metadata: ' . $approvedMetadataStatusCount);
        $this->fileOutput->writeln('accepted metadata: ' . $acceptedMetadataStatusCount);
        $this->fileOutput->writeln("\t" . 'Accepted metadata emails that do not match a Person: ' . $xmlContactsWithoutMatchingPerson);
        $this->fileOutput->writeln("\t" . 'Blank or empty email addresses found: ' . $blankXmlContactsEmail);
        $this->fileOutput->writeln('back to submitter metadata: ' . $backToSubmitterMetadataStatusCount);
        $this->fileOutput->writeln('None metadata: ' . $noneMetadataStatusCount);
        $this->fileOutput->writeln('InReview metadata ' .$InReviewMetadataStatusCount);
        $this->fileOutput->writeln('Submitted metadata ' . $SubmittedMetadataStatusCount);
        $this->fileOutput->writeln('other metadata: ' . $otherMetadataStatusCount);

        return 0;
    }

    private function printResults(OutputInterface $output = null) {
        if(count($this->fileOutputArray) >= 2) {
            $stringBuffer = "";
            for( $n = 0; $n < count($this->fileOutputArray); $n += 1) {
                if($n >= 1 ) {
                    $stringBuffer .= ",";
                }
                $stringBuffer .= $this->fileOutputArray[$n];
            }
            if($output) {
                $output->writeln($stringBuffer);
            }
            $this->fileOutput->writeln($stringBuffer);
        }
    }

    protected function getPersonEmailContacts($emailAddress) {
        $personArray = $this->entityManager->getRepository(Person::class)->findBy(
            array('emailAddress' => strtolower($emailAddress)));
        $person = null;
        if (count($personArray) > 0) {
            $person =  $personArray[0];
        }
        return $person;
    }
}