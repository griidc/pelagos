<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;

/**
 * Listener class for DIF-related events.
 */
class DIFListener
{
    /**
     * The twig templating engine instance.
     *
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * The swiftmailer instance.
     *
     * @var \Swift_Mailere $mailer
     */
    protected $mailer;

    /**
     * Person entity for the logged-in user.
     *
     * @var Person $currentUser
     */
    protected $currentUser;

    /**
     * The symfony-managed token object to traverse to current user Person.
     *
     * @var TokenStorage $tokenStorage.
     */
    protected $tokenStorage;

    /**
     * An array holding email from name/email information.
     *
     * @var Array $from
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
     * Method to send an email to user and DRPMs on a submit event.
     *
     * @param DIFEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onSubmitted(DIFEvent $event)
    {
        // Token's getUser returns an account, not a person directly.
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();

        // Traverse to dataset to get Udi.
        $udi = $event->getDIF()->getDataset()->getUdi();

        // email DRPM(s)
        $drpms = $this->getDRPMs($event->getDIF());
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.drpm.email.twig');
        $this->sendMailMsg($drpms, $template, $udi, 'DIF Submitted');

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.email.twig');
        $this->sendMailMsg(array($currentUser), $template, $udi, 'DIF Submitted');

    }

    /**
     * Method to send an email to the user of an approval.
     *
     * @param DIFEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onApproved(DIFEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();
        $udi = $event->getDIF()->getDataset()->getUdi();
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:approved.email.twig');
        $this->sendMailMsg(array($currentUser), $template, $udi, 'DIF Submitted');
    }

    /**
     * Method to send an email on a rejected event.
     *
     * @param DIFEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onRejected(DIFEvent $event)
    {
        $dif = $event->getDIF();
    }

    /**
     * Method to email user their DIF unlock request has been granted.
     *
     * @param DIFEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onUnlocked(DIFEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();
        $udi = $event->getDIF()->getDataset()->getUdi();
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:difUnlocked.email.html.twig');
        $this->sendMailMsg(array($currentUser), $template, $udi, 'DIF has been unlocked');
    }

    /**
     * Method to send an email on unlock request event to DRPMs.
     *
     * @param DIFEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onUnlockRequested(DIFEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson();
        $udi = $event->getDIF()->getDataset()->getUdi();
        $drpms = $this->getDRPMs($event->getDIF());
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.drpm.email.twig');
        $this->sendMailMsg($drpms, $template, $udi, 'DIF Submitted');
    }

    /**
     * Method to build and send an email.
     *
     * @param array          $peopleObjs   An array of recepient Persons.
     * @param \Twig_Template $twigTemplate A twig template.
     * @param string         $udi          UDI of a dataset to include in email message.
     * @param string         $subject      Subjet for email message.
     *
     * @return void
     */
    protected function sendMailMsg(array $peopleObjs, \Twig_Template $twigTemplate, $udi, $subject)
    {
        $mailData['udi'] = $udi;
        foreach ($peopleObjs as $person) {
            $mailData['person'] = $person;
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
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
}
