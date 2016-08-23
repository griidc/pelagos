<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\Handler\EntityHandler;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DataRepositoryRole;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDataRepository;
use Pelagos\Util\DataStore;

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
     * A variable to hold instance of Pelagos Entityhandler.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * An instance of the Pelagos Data Store utility service.
     *
     * @var DataStore
     */
    protected $dataStore;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Twig_Environment  $twig          Twig engine.
     * @param \Swift_Mailer      $mailer        Email handling library.
     * @param TokenStorage       $tokenStorage  Symfony's token object.
     * @param string             $fromAddress   Sender's email address.
     * @param string             $fromName      Sender's name to include in email.
     * @param EntityHandler|null $entityHandler Pelagos entity handler.
     * @param DataStore|null     $dataStore     An instance of the Pelagos Data Store utility service.
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TokenStorage $tokenStorage,
        $fromAddress,
        $fromName,
        EntityHandler $entityHandler = null,
        DataStore $dataStore = null
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->from = array($fromAddress => $fromName);
        $this->entityHandler = $entityHandler;
        $this->dataStore = $dataStore;
    }

    /**
     * Method to build and send an email.
     *
     * @param \Twig_Template $twigTemplate A twig template.
     * @param array          $mailData     Mail data array for email.
     * @param array|null     $peopleObjs   An optional array of recipient Persons.
     * @param array          $attachments  An optional array of Swift_Message_Attachments to attach.
     *
     * @throws \InvalidArgumentException When any element of $attachments is not a Swift_Message_Attachment.
     *
     * @return void
     */
    protected function sendMailMsg(
        \Twig_Template $twigTemplate,
        array $mailData,
        array $peopleObjs = null,
        array $attachments = array()
    ) {
        $currentToken = $this->tokenStorage->getToken();
        if ($currentToken instanceof TokenInterface) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof Account) {
                $currentPerson = $currentUser->getPerson();
                $mailData['user'] = $currentPerson;
                if (null === $peopleObjs) {
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
            foreach ($attachments as $attachment) {
                if (!$attachment instanceof \Swift_Attachment) {
                    throw new \InvalidArgumentException('Attachment is not an instance of Swift_Attachment');
                }
                $message->attach($attachment);
            }
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
        $recipientPeople = array();
        $personDataRepositories = $dataset->getResearchGroup()
                                          ->getFundingCycle()
                                          ->getFundingOrganization()
                                          ->getDataRepository()
                                          ->getPersonDataRepositories();

        foreach ($personDataRepositories as $pdr) {
            if ($pdr->getRole()->getName() == DataRepositoryRoles::MANAGER) {
                $recipientPeople[] = $pdr->getPerson();
            }
        }
        return $recipientPeople;
    }

    /**
     * Internal method to get _all_ DRPMs.
     *
     * @throws \Exception On more than one DataRepositoryRole found for MANAGER.
     *
     * @return Array of Persons having DRPM status.
     */
    protected function getAllDRPMs()
    {
        $recipientPeople = array();
        $eh = $this->entityHandler;

        $drpmRole = $eh->getBy(DataRepositoryRole::class, array('name' => DataRepositoryRoles::MANAGER));
        if (1 !== count($drpmRole)) {
            throw new \Exception('More than one role found for manager role.');
        }
        $personDataRepositories = $eh->getBy(PersonDataRepository::class, array('role' => $drpmRole[0] ));

        foreach ($personDataRepositories as $pdr) {
            $recipientPeople[] = $pdr->getPerson();
        }

        return $recipientPeople;
    }

    /**
     * Method to resolve Data Managers from a Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the DIF.
     */
    public function getDatasetDMs(Dataset $dataset)
    {
        $recipientPeople = array();
        $personResearchGroups = $dataset->getResearchGroup()->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                $recipientPeople[] = $prg->getPerson();
            }
        }
        return $recipientPeople;
    }

    /**
     * Method to resolve a person's Data Managers.
     *
     * @param Person $person A Person object.
     *
     * @return Array of Persons who are Data Managers for the Person passed in.
     */
    public function getPersonDMs(Person $person)
    {
        $recipientPeople = array();
        $personResearchGroups = $person->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                $recipientPeople[] = $prg->getPerson();
            }
        }
        return $recipientPeople;
    }

    /**
     * Method to resolve all DMs associated with a Person and Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     * @param Person $person A Person entity.
     *
     * @return Array of Persons who are Data Managers associated with the Person or Dataset.
     */
    public function getDMs(Dataset $dataset, Person $person)
    {
        return array_unique(array_merge($this->getDatasetDMs($dataset), $this->getPersonDMs($person)));
    }
}
