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
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

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
            ->setDescription('Description: reports:missing-primary-contants-command');
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


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file   = '/home/jholland/pelagos/scratch/missing-primary-contacts.txt';
        $handle = fopen($file, 'w');
        $output = new StreamOutput($handle);
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * $datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));
         */
        $datasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findAll();

        if (count($datasets) == 0) {
            throw new \Exception('Could not find all the datasets');
        }

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
        $output->writeln("\nDatasets in which metadata xml email address does not match a person in the system");
        $output->writeln("\nNumber,Dataset ID,Metadata XML email address: ");
        $badCount = 0;
        foreach ( $datasets as $dataset) {
            $outputArray = array();
            $outputArray[] = $dataset->getUdi();
            $status = $dataset->getMetadataStatus();
            $datasetSubmission = $dataset->getDatasetSubmission();
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
                        if ($datasetSubmission ) {
                            $emailFromXml = ISOMetadataExtractorUtil::extractContactEmailAddresses(
                                $simpleXml,
                                $datasetSubmission,
                                $this->entityManager);
                            if(count($emailFromXml) >= 0) {
                                foreach ($emailFromXml as $emailAddr) {
                                    if(strlen($emailAddr) == 0 || $emailAddr  == '') {
                                        $outputArray[] = "** Blank **" ;
                                        $blankXmlContactsEmail += 1;
                                    } else {
                                        $person = $this->getPersonEmailContacts($emailAddr);
                                        if ($person == null) {
                                            $xmlContactsWithoutMatchingPerson += 1;
                                            $outputArray[] = $emailAddr;
                                        }
                                    }
                                }
                            } else {
                                $output->writeln("No email addresses returned in XML metadata");
                            }
                        } else {
                            $output->writeln("NO DatasetSubmission");
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

            if(count($outputArray) >= 2) {
                $badCount += 1;
                $stringBuffer = "" . $badCount . ",";
                for( $n = 0; $n < count($outputArray); $n += 1) {
                    if($n >= 1 ) {
                        $stringBuffer .= ",";
                    }
                    $stringBuffer .= $outputArray[$n];
                }
                $output->writeln($stringBuffer);
            }
        }

        $output->writeln('Datasetsets found: ' . count($datasets));
        $output->writeln('approved metadata: ' . $approvedMetadataStatusCount);
        $output->writeln('accepted metadata: ' . $acceptedMetadataStatusCount);
        //$output->writeln("\t" . 'With Metadata count: ' . $hasMetadataCount);
        //$output->writeln("\t" . 'With XML count: ' . $hasMetadataXmlCount);
        $output->writeln("\t" . 'Accepted metadata emails that do not match a Person: ' . $xmlContactsWithoutMatchingPerson);
        $output->writeln("\t" . 'Blank or empty email addresses for: ' . $blankXmlContactsEmail);
        $output->writeln('back to submitter metadata: ' . $backToSubmitterMetadataStatusCount);
        $output->writeln('None metadata: ' . $noneMetadataStatusCount);
        $output->writeln('InReview metadata ' .$InReviewMetadataStatusCount);
        $output->writeln('Submitted metadata ' . $SubmittedMetadataStatusCount);
        $output->writeln('other metadata: ' . $otherMetadataStatusCount);

        $total = $approvedMetadataStatusCount + $acceptedMetadataStatusCount + $backToSubmitterMetadataStatusCount +
            $noneMetadataStatusCount + $InReviewMetadataStatusCount + $SubmittedMetadataStatusCount + $otherMetadataStatusCount;
        // $output->writeln(' total metadata: ' . $total);


        #$datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        #$this->entityManager->persist($datasetSubmission);
        #$this->entityManager->flush();

        return 0;
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