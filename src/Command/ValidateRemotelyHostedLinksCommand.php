<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Address;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Util\UrlValidation;
use App\Util\MailSender;
use Twig\Environment;

/**
 * Command to validate the links of remotely hosted datasets.
 *
 * @see Command
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:validate-remotely-hosted', description: 'Validate links for remotely hosted datasets.')]
class ValidateRemotelyHostedLinksCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Custom swiftmailer instance.
     *
     * @var MailSender
     */
    protected $mailer;

    /**
     * Twig environment instance.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Url validation utility class instance.
     *
     * @var UrlValidation
     */
    protected $urlValidation;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine ORM EntityManager instance.
     * @param MailSender             $mailer        Custom swiftmailer instance.
     * @param Environment            $twig          Twig environment instance.
     * @param UrlValidation          $urlValidation Url validation utility class instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MailSender $mailer,
        Environment $twig,
        UrlValidation $urlValidation
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->urlValidation = $urlValidation;
        parent::__construct();
    }


    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
            ['availabilityStatus' =>
                [DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED]]
        );

        foreach ($datasets as $dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();

            if ($datasetSubmission instanceof DatasetSubmission) {
                $link = $datasetSubmission->getRemotelyHostedUrl();
                $httpResponse = $this->urlValidation->validateUrl($link);

                if ($httpResponse === true) {
                    $httpCode = 200;
                } else {
                    $httpCode = (trim(str_replace('Could not get URL, returned HTTP code', '', $httpResponse)));
                    $error['udi'] = $dataset->getUdi();
                    $error['link'] = $link;
                    $errors[] = $error;
                }
                $datasetSubmission->setDatasetFileUrlStatusCode($httpCode);
                $datasetSubmission->setDatasetFileUrlLastCheckedDate(new \DateTime('now', new \DateTimeZone('UTC')));
            }
            $this->entityManager->persist($datasetSubmission);
            $this->entityManager->flush();
        }

        if (!empty($errors)) {
            $this->mailer->sendEmailMessage(
                $this->twig->load('Email/data-repository-managers.error-remotely-hosted.email.twig'),
                ['errors' => $errors],
                [new Address('help@griidc.org', 'GRIIDC')],
            );
        }

        return Command::SUCCESS;
    }
}
