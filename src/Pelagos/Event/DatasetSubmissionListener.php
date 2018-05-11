<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Entity;
use Pelagos\Entity\Person;

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

        // Publish message requesting DOI generation.
        // Producer passed in via constructor is that of the doi_issue producer.
        $this->producer->publish($dataset->getId(), 'issue');

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');

        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-submitted.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getDMs($dataset, $datasetSubmission->getSubmitter())
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
        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));

        // email DM(s)
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-updated.email.twig');
        $this->sendMailMsg(
            $template,
            array('dataset' => $dataset),
            $this->getDMs($dataset, $datasetSubmission->getSubmitter())
        );

        // email DRPM(s)
        $template = $this->twig
            ->loadTemplate('PelagosAppBundle:Email:data-repository-managers.dataset-resubmitted.email.twig');
        $this->sendMailMsg(
            $template,
            array('datasetSubmission' => $datasetSubmission),
            $this->getAllDRPMs()
        );
    }

    /**
     * Method to send an email to DRPM on a dataset_processed event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDatasetProcessed(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // Added if-statement so that emails are sent to data-managers only when a dataset is submitted
        // and not when a review is ended.
        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            //email DRMs
            $this->sendMailMsg(
                $this->twig->loadTemplate('PelagosAppBundle:Email:data-repository-managers.dataset-processed.email.twig'),
                array('datasetSubmission' => $datasetSubmission),
                $this->getDRPMs($datasetSubmission->getDataset())
            );
        }

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
     * Method called when review is started in review mode.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onStartReview(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();
        $datasetSubmissionPrev = $dataset->getDatasetSubmissionHistory()->first();
        // when there is no state change, should not log the status.
        if ($datasetSubmissionPrev->getMetadataStatus() === $datasetSubmission->getMetadataStatus()) {
            $this->mdappLogger->writeLog($datasetSubmission->getModifier()->getAccount()->getUsername() .
                ' started review for ' . $dataset->getUdi());
        } else {
            $this->mdappLogger->writeLog($datasetSubmission->getModifier()->getAccount()->getUsername() .
                ' started review for ' . $dataset->getUdi() . ' (' . $datasetSubmissionPrev->getMetadataStatus() .
                ' ->InReview)');
        }
    }

    /**
     * Method called when review is ended in review mode.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onEndReview(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();
        $this->mdappLogger->writeLog(
            $datasetSubmission->getModifier()->getAccount()->getUsername() .
            ' ended review for ' . $dataset->getUdi()
        );
    }

    /**
     * Method called when review is accepted in review mode.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onAcceptReview(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();
        $this->mdappLogger->writeLog(
            $datasetSubmission->getModifier()->getAccount()->getUsername() .
            ' accepted dataset ' . $dataset->getUdi() . ' (In Review->Accepted)'
        );
    }

    /**
     * Method called when requested revisions for a dataset in review mode.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onRequestRevisions(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();
        $this->mdappLogger->writeLog(
            $datasetSubmission->getModifier()->getAccount()->getUsername()
             . ' requested revisions for ' . $dataset->getUdi() . ' (In Review->Request Revisions)'
        );
    }
}
