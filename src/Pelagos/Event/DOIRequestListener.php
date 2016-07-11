<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;
use Pelagos\Bundle\AppBundle\Handler\EntityHandler;

use Pelagos\Entity\Account;
use Pelagos\Entity\DoiRequest;
use Pelagos\Entity\Person;

/**
 * Listener class for DOI-related events.
 */
class DOIRequestListener extends EventListener
{
    /**
     * A variable to hold instance of Pelagos Entityhandler.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * This is the overriden base class constructor to handle D.I. of EntityHandler.
     *
     * @param \Twig_Environment $twig          Twig engine.
     * @param \Swift_Mailer     $mailer        Email handling library.
     * @param TokenStorage      $tokenStorage  Symfony's token object.
     * @param string            $fromAddress   Sender's email address.
     * @param string            $fromName      Sender's name to include in email.
     * @param EntityHandler     $entityHandler Pelagos entity handler.
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TokenStorage $tokenStorage,
        $fromAddress,
        $fromName,
        EntityHandler $entityHandler
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->from = array($fromAddress => $fromName);
        $this->entityHandler = $entityHandler;
    }

    /**
     * Method to send an email to user, DRPMs, and DMs on a DOI issued event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDoiIssued(EntityEvent $event)
    {
        $doiRequest = $this->getDoiRequest($event);

        // email User
        $template = $this->twig->loadTemplate('@Email/DoiRequest/user.doi-approved.email.twig');
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), array($doiRequest->getCreator()));

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/DoiRequest/data-managers.doi-approved.email.twig');
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getDMsFromDoiRequest($doiRequest));
    }

    /**
     * Method to send an email to Approvers (DRPMs), and DMs on a DOI requested event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDoiRequested(EntityEvent $event)
    {
        $doiRequest = $this->getDoiRequest($event);

        // email Reviewers (DRPMs)
        $template = $this->twig->loadTemplate('@Email/DoiRequest/reviewers.doi-requested.email.twig');
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getAllDRPMs());

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/DoiRequest/data-managers.doi-requested.email.twig');
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getDMsFromDoiRequest($doiRequest));
    }

    /**
     * Internal method to resolve Data Managers from a DoiRequest.
     *
     * @param DoiRequest $doiRequest A DoiRequest entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the DoiRequest.
     */
    protected function getDMsFromDoiRequest(DoiRequest $doiRequest)
    {
        $recepientPeople = array();
        $researchGroups = $doiRequest->getCreator()->getResearchGroups();

        foreach ($researchGroups as $rg) {
            $prgs = $rg->getPersonResearchGroups();
            foreach ($prgs as $prg) {
                if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                    $recepientPeople[] = $prg->getPerson();
                }
            }
        }
        return $recepientPeople;
    }

    /**
     * Internal method to get a DOI object from an Event.
     *
     * @param EntityEvent $event The event listener is for.
     *
     * @throws \Exception If event passed is not an instance of DOI (bad usage).
     *
     * @return DOI A DOI object associated with the event being listened on.
     */
    protected function getDOIRequest(EntityEvent $event)
    {
        $doiRequest = $event->getEntity();
        if (DoiRequest::class != get_class($doiRequest)) {
            throw new \Exception('Internal error: handler expects a DoiRequest');
        }
        return $doiRequest;
    }
}
