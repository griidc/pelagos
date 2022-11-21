<?php

namespace App\Event;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Handler\EntityHandler;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DataRepositoryRole;
use App\Entity\Person;
use App\Entity\PersonDataRepository;
use App\Entity\ResearchGroupRole;
use App\Util\MailSender;
use Twig\Environment;
use App\Util\DataStore;
use App\Util\MdappLogger;

/**
 * Listener class for Dataset Submission-related events.
 */
abstract class EventListener
{
    /**
     * The twig templating engine instance.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * The swiftmailer instance.
     *
     * @var MailSender
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
     * An MDAPP logger.
     *
     * @var MdappLogger
     */
    protected $mdappLogger;

    /**
     * The Message Bus.
     *
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param Environment                 $twig          Twig engine.
     * @param MailSender                  $mailer        Email handling library.
     * @param TokenStorageInterface       $tokenStorage  Symfony's token object.
     * @param EntityHandler|null          $entityHandler Pelagos entity handler.
     * @param DataStore|null              $dataStore     An instance of the Pelagos Data Store utility service.
     * @param MdappLogger|null            $mdappLogger   An MDAPP logger.
     * @param MessageBusInterface|null    $messageBus    Symfony messenger bus interface instance.
     * @param EntityManagerInterface|null $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        Environment $twig,
        MailSender $mailer,
        TokenStorageInterface $tokenStorage,
        EntityHandler $entityHandler = null,
        DataStore $dataStore = null,
        MdappLogger $mdappLogger = null,
        MessageBusInterface $messageBus = null,
        EntityManagerInterface $entityManager = null
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->entityHandler = $entityHandler;
        $this->dataStore = $dataStore;
        $this->mdappLogger = $mdappLogger;
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    /**
     * Method to build and send an email.
     *
     * @param \Twig\TemplateWrapper $twigTemplate A twig template.
     * @param array                 $mailData     Mail data array for email.
     * @param array|null            $peopleObjs   An optional array of recipient Persons.
     *
     * @return void
     */
    protected function sendMailMsg(
        \Twig\TemplateWrapper $twigTemplate,
        array $mailData,
        array $peopleObjs = null,
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

        foreach (array_unique($peopleObjs, SORT_REGULAR) as $person) {
            $mailData['recipient'] = $person;
            $this->mailer->sendEmailMessage(
                $twigTemplate,
                $mailData,
                array(new Address($person->getEmailAddress(), $person->getFirstName() . ' ' . $person->getLastName())),
            );
        }
    }

    /**
     * Internal method to resolve DRPMs from a Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     *
     * @return array Array of Persons having DRPM status.
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
            if ($pdr->getRole()->getName() == DataRepositoryRole::MANAGER) {
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
     * @return array Array of Persons having DRPM status.
     */
    protected function getAllDRPMs()
    {
        $recipientPeople = array();
        $eh = $this->entityHandler;

        $drpmRole = $eh->getBy(DataRepositoryRole::class, array('name' => DataRepositoryRole::MANAGER));

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
     * @return array Array of Persons who are Data Managers for the Research Group tied back to the DIF.
     */
    protected function getDatasetDMs(Dataset $dataset)
    {
        $recipientPeople = array();
        $personResearchGroups = $dataset->getResearchGroup()->getPersonResearchGroups();

        foreach ($personResearchGroups as $prg) {
            if ($prg->getRole()->getName() == ResearchGroupRole::DATA) {
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
     * @return array of Persons who are Data Managers for the Person passed in.
     */
    protected function getPersonDMs(Person $person)
    {
        $recipientPeople = array();
        $researchGroups = $person->getResearchGroups();

        foreach ($researchGroups as $rg) {
            $prgs = $rg->getPersonResearchGroups();
            foreach ($prgs as $prg) {
                if ($prg->getRole()->getName() == ResearchGroupRole::DATA) {
                    $recipientPeople[] = $prg->getPerson();
                }
            }
        }

        return $recipientPeople;
    }

    /**
     * Method to resolve all DMs associated with a Person and Dataset.
     *
     * @param Dataset $dataset A Dataset entity.
     * @param Person  $person  A Person entity.
     *
     * @return array of Persons who are Data Managers associated with the Person or Dataset.
     */
    protected function getDMs(Dataset $dataset, Person $person)
    {
        return array_unique(array_merge($this->getDatasetDMs($dataset), $this->getPersonDMs($person)), SORT_REGULAR);
    }
}
