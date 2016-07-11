<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\DataRepositoryRole;
use Pelagos\Entity\DoiRequest;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDataRepository;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;
use Pelagos\Bundle\AppBundle\Handler\EntityHandler;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Listener class for DOI-related events.
 */
class DOIRequestListener
{
    /**
     * The twig templating engine instance.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * The swiftmailer instance.
     *
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * Person entity for the logged-in user.
     *
     * @var Person
     */
    protected $currentUser;

    /**
     * The symfony-managed token object to traverse to current user Person.
     *
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * An array holding email from name/email information.
     *
     * @var array
     */
    protected $from;

    /**
     * A variable to hold instance of Pelagos Entityhandler.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * This is the class constructor to handle dependency injections.
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
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getDMs($doiRequest));
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
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getDRPMs());

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/DoiRequest/data-managers.doi-requested.email.twig');
        $this->sendMailMsg($template, array('doiRequest' => $doiRequest), $this->getDMs($doiRequest));
    }

    /**
     * Internal method to get all DRPMs.
     *
     * @throws \Exception On more than one DataRepositoryRole found for MANAGER.
     *
     * @return Array of Persons having DRPM status.
     */
    protected function getDRPMs()
    {
        $recepientPeople = array();
        $eh = $this->entityHandler;

        $drpmRole = $eh->getBy(DataRepositoryRole::class, array('name' => DataRepositoryRoles::MANAGER));
        if (1 !== count($drpmRole)) {
            throw new \Exception('More than one role found for manager role.');
        }
        $personDataRepositories = $eh->getBy(PersonDataRepository::class, array('role' => $drpmRole[0] ));

        foreach ($personDataRepositories as $pdr) {
            $recepientPeople[] = $pdr->getPerson();
        }

        return $recepientPeople;
    }

    /**
     * Internal method to resolve Data Managers from a DoiRequest.
     *
     * @param DoiRequest $doiRequest A DoiRequest entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the DoiRequest.
     */
    protected function getDMs(DoiRequest $doiRequest)
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
