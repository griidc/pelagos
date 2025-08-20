<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Exception\NotDeletableException;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * Entity class to represent a Person.
 *
 *
 *
 *
 */
#[ORM\Entity(repositoryClass: 'App\Repository\PersonRepository')]
#[Assert\GroupSequence(['id', 'unique_id', 'Person', 'Entity'])]
#[UniqueEntity(fields: ['emailAddress'], errorPath: 'emailAddress', message: 'A Person with this email address already exists')]
class Person extends Entity
{
    use IdTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person';

    /**
     * Person's first name.
     *
     * @var string $firstName
     *
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'First name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['director', 'person'])]
    #[Assert\NotBlank(message: 'First name is required')]
    #[Groups('grp-people-accounts-report')]
    protected $firstName;

    /**
     * Person's last name.
     *
     * @var string $lastName
     *
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Last name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext')]
    #[Serializer\Groups(['director', 'person'])]
    #[Assert\NotBlank(message: 'Last name is required')]
    #[Groups('grp-people-accounts-report')]
    protected $lastName;

    /**
     * Person's email address.
     *
     * @var string $emailAddress
     *
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Email address cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', unique: true)]
    #[Serializer\Groups(['person'])]
    #[Assert\NotBlank(message: 'Email address is required')]
    #[Assert\Email(message: 'Email address is invalid', mode: 'strict')]
    protected $emailAddress;

    /**
     * Person's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Phone number cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $phoneNumber;

    /**
     * Person's delivery point (street address).
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Delievery point (address) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $deliveryPoint;

    /**
     * Person's city.
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'City cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * Person's administrative area (state).
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Administrative area (state) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $administrativeArea;

    /**
     * Person's postal code (zipcode).
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Postal code (zip) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postalCode;

    /**
     * Person's country.
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Country cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * Person's Website url.
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Website URL cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Person's organization.
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Organization cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['director', 'person'])]
    protected $organization;

    /**
     * Person's position.
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Position cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $position;

    /**
     * Person's PersonFundingOrganizations.
     *
     * @var Collection $personFundingOrganizations
     *
     * @access protected
     */
    #[ORM\OneToMany(targetEntity: 'PersonFundingOrganization', mappedBy: 'person')]
    protected $personFundingOrganizations;

    /**
     * Person's PersonResearchGroups.
     *
     * @var Collection $personResearchGroups
     *
     * @access protected
     */
    #[ORM\OneToMany(targetEntity: 'PersonResearchGroup', mappedBy: 'person')]
    protected $personResearchGroups;

    /**
     * Person's PersonDataRepositories.
     *
     * @var Collection $personDataRepositories
     *
     * @access protected
     */
    #[ORM\OneToMany(targetEntity: 'PersonDataRepository', mappedBy: 'person')]
    protected $personDataRepositories;

    /**
     * Person's Account.
     *
     * @var Account $account
     *
     * @access protected
     *
     */
    #[ORM\OneToOne(targetEntity: Account::class, mappedBy: 'person')]
    #[Serializer\Exclude]
    protected $account;

    /**
     * Person's Token.
     *
     * @var PersonToken $token
     *
     * @access protected
     *
     */
    #[ORM\OneToOne(targetEntity: 'PersonToken', mappedBy: 'person')]
    #[Serializer\Exclude]
    protected $token;

    /**
     * Constructor that initializes Collections as empty ArrayCollections.
     */
    public function __construct()
    {
        $this->personDataRepositories = new ArrayCollection();
        $this->personFundingOrganizations = new ArrayCollection();
        $this->personResearchGroups = new ArrayCollection();
    }

    /**
     * Setter for firstName property.
     *
     * @param string $firstName First name of the Person.
     *
     * @return void
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Getter for firstName property.
     *
     * @return string First name of the Person.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Setter for lastName property.
     *
     * @param string $lastName Last name of the Person.
     *
     * @return void
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Getter for lastName property.
     *
     * @return string Last name of the Person.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Setter for emailAddress property.
     *
     * @param string $emailAddress Email address of the Person.
     *
     * @return void
     */
    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress property.
     *
     * @return string Email address of the Person.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string|null $phoneNumber Person's phone number.
     *
     * @access public
     *
     * @return void
     */
    public function setPhoneNumber(?string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Getter for phoneNumber.
     *
     * @access public
     *
     * @return string Phone number of Person.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string|null $deliveryPoint Street address of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setDeliveryPoint(?string $deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    /**
     * Getter for deliveryPoint.
     *
     * @access public
     *
     * @return string Street address of Person.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string|null $city City of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setCity(?string $city)
    {
        $this->city = $city;
    }

    /**
     * Getter for city.
     *
     * @access public
     *
     * @return string City of Person.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string|null $administrativeArea Person's administrative area (state).
     *
     * @access public
     *
     * @return void
     */
    public function setAdministrativeArea(?string $administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * Getter for administrativeArea.
     *
     * @access public
     *
     * @return string Person's administrative area (state).
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for postalCode.
     *
     * @param string|null $postalCode Postal (zip) code.
     *
     * @access public
     *
     * @return void
     */
    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Getter for postalCode.
     *
     * @access public
     *
     * @return string Containing postal (zip) code.
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for country.
     *
     * @param string|null $country Person's country.
     *
     * @access public
     *
     * @return void
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    /**
     * Getter for country.
     *
     * @access public
     *
     * @return string Person's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for url.
     *
     * @param string|null $url Person's Website URL.
     *
     * @access public
     *
     * @return void
     */
    public function setUrl(?string $url)
    {
        $this->url = $url;
    }

    /**
     * Getter for url.
     *
     * @access public
     *
     * @return string URL of Person's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for organization.
     *
     * @param string|null $organization Person's organization.
     *
     * @access public
     *
     * @return void
     */
    public function setOrganization(?string $organization)
    {
        $this->organization = $organization;
    }

    /**
     * Getter for organization.
     *
     * @access public
     *
     * @return string Person's organization.
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Setter for position.
     *
     * @param string|null $position Person's position.
     *
     * @access public
     *
     * @return void
     */
    public function setPosition(?string $position)
    {
        $this->position = $position;
    }

    /**
     * Getter for position.
     *
     * @access public
     *
     * @return string Person's position.
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Getter for personFundingOrganizations.
     *
     * @access public
     *
     * @return Collection Funding Organization associations for this Person.
     */
    public function getPersonFundingOrganizations()
    {
        return $this->personFundingOrganizations;
    }

    /**
     * Getter for fundingOrganizations.
     */
    public function getFundingOrganizations(): Collection
    {
        $fundingOrganizations = new ArrayCollection();
        foreach ($this->getResearchGroups() as $researchGroup) {
            $fundingOrganization = $researchGroup->getFundingOrganization();
            if ($fundingOrganization instanceof FundingOrganization) {
                if (!$fundingOrganizations->contains($fundingOrganization)) {
                    $fundingOrganizations->add($fundingOrganization);
                }
            }
        }
        return $fundingOrganizations;
    }

    /**
     * Get a list of Funding Cycles this person is associated with through their Research Groups.
     */
    public function getFundingCycles(): Collection
    {
        $fundingCycles = new ArrayCollection();
        foreach ($this->getResearchGroups() as $researchGroup) {
            $fundingCycle = $researchGroup->getFundingCycle();
            if ($fundingCycle instanceof FundingCycle) {
                if (!$fundingCycles->contains($fundingCycle)) {
                    $fundingCycles->add($fundingCycle);
                }
            }
        }
        return $fundingCycles;
    }

    /**
     * Getter for personResearchGroups.
     *
     * @access public
     *
     * @return Collection Research Group associations for this Person.
     */
    public function getPersonResearchGroups()
    {
        return $this->personResearchGroups;
    }

    /**
     * Getter for PersonDataRepositories.
     *
     * @access public
     *
     * @return Collection Data Repository associations for this Person.
     */
    public function getPersonDataRepositories()
    {
        return $this->personDataRepositories;
    }

    /**
     * Getter for DataRepositories.
     *
     * @access public
     *
     * @return ArrayCollection Data Repositories this Person is associated with.
     */
    public function getDataRepositories()
    {
        $personDataRepositories = $this->getPersonDataRepositories();
        $collection = new ArrayCollection();
        foreach ($personDataRepositories as $personDataRepository) {
            $collection->add($personDataRepository->getDataRepository());
        }
        return $collection;
    }

    /**
     * Get a list of the names of all Data Repositories this person is associated with.
     *
     * @return array
     */
    public function getDataRepositoryNames()
    {
        $dataRepositoryNames = array();
        foreach ($this->personDataRepositories as $personDataRepository) {
            $dataRepositoryNames[] = $personDataRepository->getDataRepository()->getName();
        }
        return $dataRepositoryNames;
    }

    /**
     * Setter for account.
     *
     * @param Account|null $account Account to attach to this person.
     *
     * @access public
     *
     * @return void
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;
        if ($this->account !== null and $this->account->getPerson() !== $this) {
            $this->account->setPerson($this);
        }
    }

    /**
     * Getter for account.
     *
     * @access public
     *
     * @return Account|null Account that is attached to this person.
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Setter for token.
     *
     * @param PersonToken $token Person's token.
     *
     * @access public
     *
     * @return void
     */
    public function setToken(PersonToken $token = null)
    {
        $this->token = $token;
        if ($this->token !== null and $this->token->getPerson() !== $this) {
            $this->token->setPerson($this);
        }
    }

    /**
     * Getter for token.
     *
     * @access public
     *
     * @return PersonToken Person's token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Check if this Person is deletable.
     *
     * This method throws a NotDeletableException when the Person has associated FundingOrganizations or
     * ResearchGroups. The NotDeletableException will have its reasons set to a list of reasons the Person
     * is not deletable.
     *
     * @throws NotDeletableException When the Person has associated FundingOrganizations or ResearchGroups.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();

        $personFundingOrganizationCount = count($this->getPersonFundingOrganizations());
        if ($personFundingOrganizationCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personFundingOrganizationCount > 1 ? 'are' : 'is') .
                " $personFundingOrganizationCount associated Funding Organization" .
                ($personFundingOrganizationCount > 1 ? 's' : '');
        }

        $personResearchGroupCount = count($this->getPersonResearchGroups());
        if ($personResearchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personResearchGroupCount > 1 ? 'are' : 'is') .
                " $personResearchGroupCount associated Research Group" .
                ($personResearchGroupCount > 1 ? 's' : '');
        }

        $personDataRepositoriesCount = count($this->getPersonDataRepositories());
        if ($personDataRepositoriesCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personDataRepositoriesCount > 1 ? 'are' : 'is') .
                " $personDataRepositoriesCount associated Data " .
                ($personDataRepositoriesCount > 1 ? 'Repositories' : 'Repository');
        }

        if (is_countable($this->getAccount())) {
            $personAccountCount = count($this->getAccount());
            if ($personAccountCount > 0) {
                $notDeletableReasons[] = 'there is an associated Account';
            }
        }

        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }

    public function getFullName(): string
    {
        return trim($this->getLastName() . ', ' . $this->getFirstName());
    }

    /**
     * Return the id as a string when converting a Person to a string.
     *
     * This is needed for serialization of the Account entity.
     *
     * @return string String representation of a Person object.
     */
    public function __toString()
    {
        return $this->getFullName() . ' (' . ($this->getEmailAddress()) . ')';
    }

    /**
     * Get a list of the names of all Research Groups this person is associated with.
     *
     * @return array
     */
    public function getResearchGroupNames()
    {
        $researchGroupNames = array();
        foreach ($this->personResearchGroups as $personResearchGroup) {
            $researchGroupNames[] = $personResearchGroup->getResearchGroup()->getName();
        }
        return $researchGroupNames;
    }

    /**
     * Get all Research Groups this person is associated with.
     *
     * @return array
     */
    public function getResearchGroups()
    {
        $researchGroups = array();
        foreach ($this->personResearchGroups as $personResearchGroup) {
            $researchGroups[] = $personResearchGroup->getResearchGroup();
        }
        return $researchGroups;
    }

    /**
     * Get all datasets for this person for each research group they are in.
     */
    public function getDatasets(): Collection
    {
        $datasets = new ArrayCollection();
        foreach ($this->personResearchGroups as $personResearchGroup) {
            foreach ($personResearchGroup->getResearchGroup()->getDatasets() as $dataset) {
                if (!$datasets->contains($dataset)) {
                    $datasets->add($dataset);
                }
            }
        }
        return $datasets;
    }

    /**
     * Get publications for this perons.
     */
    public function getPublications(): Collection
    {
        $publications = new ArrayCollection();
        foreach ($this->getDatasets() as $dataset) {
            foreach ($dataset->getPublications() as $publication) {
                if (!$publications->contains($publication)) {
                    $publications->add($publication);
                }
            }
        }
        return $publications;
    }

    /**
     * Does this person have an account?
     */
    #[Groups(['grp-people-accounts-report'])]
    #[SerializedName('hasAccount')]
    public function getHasAccount(): string
    {
        return ($this->getAccount() instanceof Account) ? 'yes' : 'no';
    }

    /**
     * Get the account creation time.
     */
    #[Groups(['grp-people-accounts-report'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public function getAccountCreationDate(): ?\DateTime
    {
        return $this->getAccount()?->getCreationTimeStamp();
    }
}
