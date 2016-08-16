<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\DIF;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

/**
 * Listener class for DIF-related events.
 */
class DIFListener
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
        $this->currentUser = $tokenStorage->getToken()->getUser()->getPerson();
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

        // email Reviewers
        $template = $this->twig->loadTemplate('@DIFEmail/reviewers/reviewers.dif-submitted.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDRPMs($dif));

        // email User
        $template = $this->twig->loadTemplate('@DIFEmail/user/user.dif-submitted.email.twig');
        $this->sendMailMsg($template, $dif);

        // email Data Managers
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-submitted.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDMsFromPerson(Person $this->currentUser));
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
        $template = $this->twig->loadTemplate('@DIFEmail/user/user.dif-approved.email.twig');
        $this->sendMailMsg($template, $dif);

        // email DM
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-approved.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDMs($dif));
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
        $template = $this->twig->loadTemplate('@DIFEmail/user/user.dif-unlocked.email.twig');
        $this->sendMailMsg($template, $dif);

        // email data managers
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-unlocked.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDMsFromPerson(Person $this->currentUser));
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

        // email reviewers
        $template = $this->twig->loadTemplate('@DIFEmail/reviewers/reviewers.dif-unlock-requested.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDRPMs($dif));

        // email DM
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-unlock-requested.email.twig');
        $this->sendMailMsg($template, $dif, $this->getDMs($dif));
    }

    /**
     * Method to email data managers when a DIF is created.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onCreated(EntityEvent $event)
    {
        // email DM
        $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-created.email.twig');
        $this->sendMailMsg($template, $this->getDIF($event), $this->getDMsFromPerson(Person $this->currentUser));
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
     * Internal method to resolve DRPMs from a dif.
     *
     * @param DIF $dif A DIF entity.
     *
     * @return Array of Persons having DRPM status.
     */
    protected function getDRPMs(DIF $dif)
    {
        $recepientPeople = array();
        $personDataRepositories = $dif->getResearchGroup()
                                      ->getFundingCycle()
                                      ->getFundingOrganization()
                                      ->getDataRepository()
                                      ->getPersonDataRepositories();

        foreach ($personDataRepositories as $pdr) {
            if ($pdr->getRole()->getName() == DataRepositoryRoles::MANAGER) {
                $recepientPeople[] = $pdr->getPerson();
            }
        }
        return $recepientPeople;
    }

    /**
     * Internal method to resolve Data Managers from a dif.
     *
     * @param DIF $dif A DIF entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the DIF.
     */
    protected function getDMs(DIF $dif)
    {
        $recepientPeople = array();
        $personResearchGroups = $dif->getResearchGroup()->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                $recepientPeople[] = $prg->getPerson();
            }
        }
        return $recepientPeople;
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
