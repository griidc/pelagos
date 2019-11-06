<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DataRepositoryRole;
use Pelagos\Entity\PersonDataRepository;

use Pelagos\Util\UrlValidation;

/**
 * Command to validate the links of remotely hosted datasets.
 *
 * @see ContainerAwareCommand
 */
class ValidateRemotelyHostedLinksCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
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
                $httpResponse = $urlValidationService->validateUrl($datasetSubmission->getDatasetFileUri());
                if ($httpResponse === true) {
                    $httpCode = 200;
                } else {
                    $httpCode = (trim(str_replace('Could not get URL, returned HTTP code', '', $httpResponse)));
                    array_push($errorUdi, $dataset->getUdi());
                }
                $datasetSubmission->setDatasetFileUrlStatusCode($httpCode);
                $datasetSubmission->setDatasetFileUrlLastCheckedDate(new \DateTime('now', new \DateTimeZone('UTC')));
            }
            $entityManager->persist($datasetSubmission);
            $entityManager->flush();
        }

        if (!empty($errorUdi)) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Error Log - List of Remotely Hosted Datasets links failed')
                ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
                ->setTo(array('griidc@gomri.org' => 'GRIIDC'))
                ->setCharset('UTF-8')
                ->setBody($this->getContainer()->get('templating')->render(
                    'PelagosAppBundle:Email:data-repository-managers.error-remotely-hosted.email.twig',
                    array('listOfUdi' => $errorUdi)
                ), 'text/html');
            $this->getContainer()->get('mailer')->send($message);
        }
    }
}
