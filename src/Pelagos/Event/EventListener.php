<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\Dataset;

/**
 * Listener class for Dataset Submission-related events.
 */
abstract class EventListener
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
     * Method to build and send an email.
     *
     * @param \Twig_Template $twigTemplate A twig template.
     * @param array          $mailData     Mail data array for email.
     * @param array|null     $peopleObjs   An optional array of recepient Persons.
     *
     * @return void
     */
    protected function sendMailMsg(\Twig_Template $twigTemplate, array $mailData, array $peopleObjs = null)
    {
        $currentToken = $this->tokenStorage->getToken();
        if ($currentToken instanceof TokenInterface) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof Account) {
                $currentPerson = $currentUser->getPerson();
                $mailData['user'] = $currentPerson;
                if ($peopleObjs == null) {
                    $peopleObjs = array($currentPerson);
                }
            }
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
     * Internal method to resolve DRPMs from a Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     *
     * @return Array of Persons having DRPM status.
     */
    protected function getDRPMs(Dataset $dataset)
    {
        $recepientPeople = array();
        $personDataRepositories = $dataset->getResearchGroup()
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
     * Internal method to resolve Data Managers from a Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the DIF.
     */
    protected function getDMs(Dataset $dataset)
    {
        $recepientPeople = array();
        $personResearchGroups = $dataset->getResearchGroup()->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                $recepientPeople[] = $prg->getPerson();
            }
        }
        return $recepientPeople;
    }
}
