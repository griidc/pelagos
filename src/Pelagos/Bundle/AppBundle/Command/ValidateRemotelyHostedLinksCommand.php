<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Util\UrlValidation;

/**
 * Command to validate the links of remotely hosted datasets.
 *
 * @see ContainerAwareCommand
 */
class ValidateRemotelyHostedLinksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dataset:validate-remotely-hosted')
            ->setDescription('Validate links for remotely hosted datasets.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(
            array(
                'availabilityStatus' =>
                    array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
            )
        );
        $urlValidationService = new UrlValidation();
        $errorUdi = array();

        foreach ($datasets as $dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();

            if ($datasetSubmission instanceof DatasetSubmission) {

                try {
                    $httpResponse = $urlValidationService->validateUrl($datasetSubmission->getDatasetFileUri());
                    if ($httpResponse === true) {
                        $httpCode = 200;
                    } else {
                        $httpCode = (trim(str_replace('Could not get URL, returned HTTP code','',$httpResponse)));
                        array_push($errorUdi,$dataset->getUdi());
                    }
                    $datasetSubmission->setDatasetFileUrlStatusCode($httpCode);
                } catch (\Exception $e) {
                    $output->writeln('Unable to process curl command, Error response: ' . $e->getMessage());
                }

            }
            $datasetSubmission->setDatasetFileUrlLastCheckedDate(new \DateTime('now', new \DateTimeZone('UTC')));
            $entityManager->persist($datasetSubmission);
            $entityManager->flush();
        }
    }
}