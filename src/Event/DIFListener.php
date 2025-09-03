<?php

namespace App\Event;

use App\Entity\DIF;

/**
 * Listener class for DIF-related events.
 */
class DIFListener extends EventListener
{
    /**
     * Method to send an email to user and DRPMs on a submit event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onSubmitted(EntityEvent $event)
    {
        $dif = $this->getDIF($event);

        // email Reviewers
        $template = $this->twig->load('DIF/email/reviewers/reviewers.dif-submitted.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDRPMs($dif->getDataset()));

        // email User
        $template = $this->twig->load('DIF/email/user/user.dif-submitted.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif));

        // email Data Managers
        $template = $this->twig->load('DIF/email/data-managers/data-managers.dif-submitted.email.twig');
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDatasetDMs($dif->getDataset()));
    }

    /**
     * Method to send an email to the user of an approval.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onApproved(EntityEvent $event)
    {
        $dif = $this->getDIF($event);

        // email user
        $template = $this->twig->load('DIF/email/user/user.dif-approved.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), array($dif->getCreator()));

        // email DM
        $template = $this->twig->load('DIF/email/data-managers/data-managers.dif-approved.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDMs($dif->getDataset(), $dif->getCreator()));
    }

    /**
     * Method to email user (and data managers) that their DIF unlock request has been granted.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onUnlocked(EntityEvent $event)
    {
        $dif = $this->getDIF($event);

        // email user
        $template = $this->twig->load('DIF/email/user/user.dif-unlocked.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), array($dif->getCreator()));

        // email data managers
        $template = $this->twig->load('DIF/email/data-managers/data-managers.dif-unlocked.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDatasetDMs($dif->getDataset()));
    }

    /**
     * Method to send an email on unlock request event to reviewers and data managers.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onUnlockRequested(EntityEvent $event)
    {
        $dif = $this->getDIF($event);
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();

        // email reviewers
        $template = $this->twig->load('DIF/email/reviewers/reviewers.dif-unlock-requested.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif, 'currentUser' => $currentUser), $this->getDRPMs($dif->getDataset()));

        // email DM
        $template = $this->twig->load('DIF/email/data-managers/data-managers.dif-unlock-requested.email.twig');
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDatasetDMs($dif->getDataset()));
    }

    /**
     * Method to email data managers when a DIF is saved but not submitted.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onSavedNotSubmitted(EntityEvent $event)
    {
        $dif = $this->getDIF($event);

        // email DM
        $template = $this->twig->load('DIF/email/data-managers/data-managers.dif-created.email.twig');
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();
        $this->sendMailMsg($template, array('dif' => $dif), $this->getDatasetDMs($dif->getDataset()));
    }

    /**
     * Internal method to get a DIF object from an Event.
     *
     * @param EntityEvent $event The event listener is for.
     *
     * @throws \Exception If event passed is not an instance of DIF (bad usage).
     *
     * @return DIF A DIF object associated with the event being listened on.
     */
    protected function getDIF(EntityEvent $event)
    {
        $dif = $event->getEntity();
        if (DIF::class != get_class($dif)) {
            throw new \Exception('Internal error: handler expects a DIF');
        }
        return $dif;
    }
}
