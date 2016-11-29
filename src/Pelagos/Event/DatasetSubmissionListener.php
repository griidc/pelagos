<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * Listener class for Dataset Submission-related events.
 */
class DatasetSubmissionListener extends EventListener
{
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

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');
        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-submitted.email.twig');
        $this->sendMailMsg($template, array('dataset' => $dataset), $this->getDMs($dataset, $datasetSubmission->getCreator()));
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

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');
        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-updated.email.twig');
        $this->sendMailMsg($template, array('dataset' => $dataset), $this->getDMs($dataset, $datasetSubmission->getCreator()));
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

        // email creator
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'type' => 'dataset',
            ),
            array($datasetSubmission->getCreator())
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

        // email creator
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array(
                'datasetSubmission' => $datasetSubmission,
                'type' => 'metadata',
            ),
            array($datasetSubmission->getCreator())
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
}
