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
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
            )
        );
        $urlValidationService = new UrlValidation();

        foreach ($datasets as $dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();

            if ($datasetSubmission instanceof DatasetSubmission) {
                try {
                    $result = $urlValidationService->validateUrl($datasetSubmission->getDatasetFileUri());
                } catch (\Exception $e) {
                    $this->getContainer()->get('pelagos.event.entity_event_dispatcher')->dispatch(
                        $datasetSubmission,
                        'submitted'
                    );
                }
            }
        }
    }
}