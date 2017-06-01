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

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\ISOMetadataExtractorUtil;

class MissingPrimaryContactsCommand extends ContainerAwareCommand
{
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

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

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));
         */
        $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findAll();

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
        foreach ( $datasets as $dataset) {
            $status = $dataset->getMetadataStatus();
            $datasetSubmission = $dataset->getDatasetSubmission();
            $metadata = $dataset->getMetadata();
            if( $status == 'Approved' ) {
                $approvedMetadataStatusCount += 1;
            } elseif ($status == 'Accepted' ) {
                $acceptedMetadataStatusCount += 1;
                $output->writeln("\nAccepted Dataset : " . $dataset->getUdi());
                if($metadata) {
                    $hasMetadataCount += 1;
                    $simpleXml = $metadata->getXml();
                    if ($simpleXml->asXml()) {
                        $hasMetadataXmlCount += 1;
                        $substring = substr($simpleXml->asXml(), 0, 12);
                        $output->writeln("XML : " . $substring);
                        if ($datasetSubmission ) {
                            $contacts = ISOMetadataExtractorUtil::extractPointsOfContact(
                                $simpleXml,
                                $datasetSubmission,
                                $entityManager);
                            $output->writeln("Contact count: " . count($contacts));
                            if(count($contacts) >= 0) {
                                foreach ($contacts as $contact) {
                                    $output->writeln("Contact Person: " . $contact->getPerson()->getEmailAddress());
                                }
                            } else {
                                $output->writeln("No Contacts returned from ISO");
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
        }

        $output->writeln('*** ' . count($datasets) . " data sets found.");
        $output->writeln(' approved metadata status count: ' . $approvedMetadataStatusCount);
        $output->writeln(' accepted metadata status count: ' . $acceptedMetadataStatusCount);
        $output->writeln(' back to submitter metadata status count: ' . $backToSubmitterMetadataStatusCount);
        $output->writeln(' None metadata status count: ' . $noneMetadataStatusCount);
        $output->writeln(' InReview metadata status count ' .$InReviewMetadataStatusCount);
        $output->writeln(' Submitted metadata status count ' . $SubmittedMetadataStatusCount);
        $output->writeln(' other metadata status count: ' . $otherMetadataStatusCount);
        $total = $approvedMetadataStatusCount + $acceptedMetadataStatusCount + $backToSubmitterMetadataStatusCount +
            $noneMetadataStatusCount + $InReviewMetadataStatusCount + $SubmittedMetadataStatusCount + $otherMetadataStatusCount;
        $output->writeln(' total metadata status count: ' . $total);
        $output->writeln(' has Metadata count: ' . $hasMetadataCount);
        $output->writeln(' has XML count: ' . $hasMetadataXmlCount);

        #$datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        #$entityManager->persist($datasetSubmission);
        #$entityManager->flush();

        return 0;
    }
}