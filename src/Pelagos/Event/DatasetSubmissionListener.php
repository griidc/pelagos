<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\DatasetSubmission;

/**
 * Listener class for Dataset Submission-related events.
 */
class DatasetSubmissionListener extends eventListener
{
    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Twig_Environment $twig         Twig engine.
     * @param \Swift_Mailer     $mailer       Email handling library.
     * @param TokenStorage      $tokenStorage Symfony's token object.
     * @param string            $fromAddress  Sender's email address.
     * @param string            $fromName     Sender's name to include in email.
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TokenStorage $tokenStorage,
        $fromAddress,
        $fromName
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->from = array($fromAddress => $fromName);
    }

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

        // email User
        $template = $this->twig->loadTemplate('@DIFEmail/user/user.dif-submitted.email.twig');
        $this->sendMailMsg($template, $dif);

        // email Data Managers
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-submitted.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDMs($dif));
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
    protected function getDatasetSubmission(EntityEvent $event)
    {
        $dif = $event->getEntity();
        if (DIF::class != get_class($dif)) {
            throw new \Exception('Internal error: handler expects a DIF');
        }
        return $dif;
    }
}
