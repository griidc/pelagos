<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\DoiRequest;
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
        $this->sendMailMsg($template, $doiRequest, $this->getCreator());

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/DoiRequest/data-managers.doi-approved.email.twig');
        $this->sendMailMsg($template, $doiRequest, $this->getDMs($doiRequest));
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
        $this->sendMailMsg($template, $doiRequest, $this->getDRPMs($doiRequest));

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/DoiRequest/data-managers.doi-requested.email.twig');
        $this->sendMailMsg($template, $doiRequest, $this->getDMs($doiRequest));
    }

    /**
     * Method to build and send an email.
     *
     * @param \Twig_Template $twigTemplate A twig template.
     * @param DIF            $dif          DIF of interest.
     * @param array|null     $peopleObjs   An optional array of recepient Persons.
     *
     * @return void
     */
    protected function sendMailMsg(\Twig_Template $twigTemplate, DIF $dif, array $peopleObjs = null)
    {
        // Token's getUser returns an account, not a person directly.
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();

        $mailData = array('dif' => $dif, 'user' => $currentUser);
        if ($peopleObjs == null) {
            $peopleObjs = array($currentUser);
        }

        foreach ($peopleObjs as $person) {
            $mailData['recipient'] = $person;
            $message = \Swift_Message::newInstance()
                ->setSubject($twigTemplate->renderBlock('subject', $mailData))
                ->setFrom($this->from)
                ->setTo($person->getEmailAddress())
                ->setBody($twigTemplate->renderBlock('body_html', $mailData), 'text/html')
                ->addPart($twigTemplate->renderBlock('body_text', $mailData), 'text/plain');
            $this->mailer->send($message);
        }
    }

    /**
     * Internal method to resolve DRPMs from a DoiRequest.
     *
     * @param DoiRequest $doiRequest A DoiRequest entity.
     *
     * @return Array of Persons having DRPM status.
     */
    protected function getDRPMs(DoiRequest $doiRequest)
    {
        $recepientPeople = array();
        $personDataRepositories = $doiRequest->getCreator()->getPersonDataRepositories();

        foreach ($personDataRepositories as $pdr) {
            if ($pdr->getRole()->getName() == DataRepositoryRoles::MANAGER) {
                $recepientPeople[] = $pdr->getPerson();
            }
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
        $personResearchGroups = $doiRequest->getCreator()->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                $recepientPeople[] = $prg->getPerson();
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
