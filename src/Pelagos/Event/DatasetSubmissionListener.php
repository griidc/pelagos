<?php
namespace Pelagos\Event;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Pelagos\Bundle\AppBundle\Handler\EntityHandler;
use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;

use Pelagos\Util\DataStore;
use Pelagos\Util\MdappLogger;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Listener class for Dataset Submission-related events.
 */
class DatasetSubmissionListener extends EventListener
{
    /**
     * The Service Container.
     *
     * @var Geometry
     */
    protected $container;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Twig_Environment  $twig          Twig engine.
     * @param \Swift_Mailer      $mailer        Email handling library.
     * @param TokenStorage       $tokenStorage  Symfony's token object.
     * @param string             $fromAddress   Sender's email address.
     * @param string             $fromName      Sender's name to include in email.
     * @param EntityHandler|null $entityHandler Pelagos entity handler.
     * @param Producer           $producer      An AMQP/RabbitMQ Producer.
     * @param DataStore|null     $dataStore     An instance of the Pelagos Data Store utility service.
     * @param MdappLogger|null   $mdappLogger   An MDAPP logger.
     * @param ContainerInterface $container     The Service Container.
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TokenStorage $tokenStorage,
        $fromAddress,
        $fromName,
        EntityHandler $entityHandler = null,
        Producer $producer = null,
        DataStore $dataStore = null,
        MdappLogger $mdappLogger = null,
        ContainerInterface $container = null
    ) {
          parent::__construct(
              $twig,
              $mailer,
              $tokenStorage,
              $fromAddress,
              $fromName,
              $entityHandler,
              $producer,
              $dataStore,
              $mdappLogger
          );
          $this->container = $container;
    }

    /**
     * Method to send an email to DMs on a submitted event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onSubmitted(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();

        $this->mdappLogger->writeLog(
            sprintf(
                '%s submitted a dataset for %s',
                $datasetSubmission->getModifier()->getAccount()->getUsername(),
                $dataset->getUdi()
            )
        );

        // Publish message requesting DOI generation.
        // Producer passed in via constructor is that of the doi_issue producer.
        $this->producer->publish($dataset->getId(), 'issue');
   
        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');

        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'datalandUrl' => $this->container->get('router')->generate(
                    'pelagos_app_ui_dataland_default',
                    array('udi' => $dataset->getUdi()),
                    UrlGenerator::ABSOLUTE_URL
                )
            )
        );

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-submitted.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getDMs($dataset, $datasetSubmission->getSubmitter())
        );

        // email DRPM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-repository-managers.dataset-submitted.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getAllDRPMs()
        );
    }

    /**
     * Method to send an email to DMs on a updated event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onResubmitted(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();

        $this->mdappLogger->writeLog(
            sprintf(
                '%s updated the submission for %s',
                $datasetSubmission->getModifier()->getAccount()->getUsername(),
                $dataset->getUdi()
            )
        );
        
        $this->producer->publish($dataset->getId(), 'update');

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');
        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'datalandUrl' => $this->container->get('router')->generate(
                    'pelagos_app_ui_dataland_default',
                    array('udi' => $dataset->getUdi()),
                    UrlGenerator::ABSOLUTE_URL
                )
            )
        );

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-updated.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getDMs($dataset, $datasetSubmission->getSubmitter())
        );

        // email DRPM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-repository-managers.dataset-submitted.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getAllDRPMs()
        );
    }

    /**
     * Method to send an email to user on a dataset_processed event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDatasetProcessed(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email submitter
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'type' => 'dataset',
                'datalandUrl' => $this->getDatalandUrl($datasetSubmission->getDataset()->getUdi())
            ),
            array($datasetSubmission->getSubmitter())
        );

        // email DRMs
        $this->sendMailMsg(
            $this->twig->loadTemplate('PelagosAppBundle:Email:data-repository-managers.dataset-processed.email.twig'),
            array('datasetSubmission' => $datasetSubmission),
            $this->getDRPMs($datasetSubmission->getDataset())
        );
    }

    /**
     * Method to send an email to user on a metadata_processed event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onMetadataProcessed(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email submitter
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'type' => 'metadata',
                'datalandUrl' => $this->getDatalandUrl($datasetSubmission->getDataset()->getUdi())
            ),
            array($datasetSubmission->getSubmitter())
        );

        $metadataFileInfo = $this->dataStore->getDownloadFileInfo(
            $datasetSubmission->getDataset()->getUdi(),
            'metadata'
        );

        // email DRMs
        $this->sendMailMsg(
            $this->twig->loadTemplate('PelagosAppBundle:Email:data-repository-managers.metadata-processed.email.twig'),
            array('datasetSubmission' => $datasetSubmission),
            $this->getDRPMs($datasetSubmission->getDataset()),
            array(
                \Swift_Attachment::fromPath($metadataFileInfo->getRealPath())
                    ->setFilename($datasetSubmission->getMetadataFileName())
            )
        );
    }

    /**
     * Method to send an email to DRMs when HTML was found for a dataset file.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onHtmlFound(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email DRMs
        $this->sendMailMsg(
            $this->twig->loadTemplate(
                'PelagosAppBundle:Email:data-repository-managers.html-found-for-dataset.email.twig'
            ),
            array('datasetSubmission' => $datasetSubmission),
            $this->getDRPMs($datasetSubmission->getDataset())
        );
    }

    /**
     * Method to send an email to DRMs when the submitted dataset file is unprocessable.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDatasetUnprocessable(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email DRMs
        $this->sendMailMsg(
            $this->twig->loadTemplate(
                'PelagosAppBundle:Email:data-repository-managers.dataset-unprocessable.email.twig'
            ),
            array('datasetSubmission' => $datasetSubmission),
            $this->getDRPMs($datasetSubmission->getDataset())
        );
    }

    /**
     * Method that is called to take appropriate actions when a submission has been approved (mdapp).
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onApproved(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $this->producer->publish($datasetSubmission->getDataset()->getId(), 'publish');
        $this->producer->publish($datasetSubmission->getDataset()->getId(), 'update');
    }

    /**
     * Generate a dataland url given the udi for different drupal env.
     *
     * @param string $udi Dataset's udi.
     *
     * @return string A dataland URL.
     */
    protected function getDatalandUrl($udi)
    {
        $datalandUrl = $this->container->get('router')->generate(
            'pelagos_app_ui_dataland_default',
            array('udi' => $udi),
            UrlGenerator::ABSOLUTE_URL
        );
        if (strcmp('drupal_prod', $this->container->get('kernel')->getEnvironment()) == 0) {
            return str_replace('pelagos-symfony/', '', $datalandUrl);
        }
        return $datalandUrl;
    }
}
