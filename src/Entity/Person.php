<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

use Hateoas\Configuration\Annotation as Hateoas;

use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use App\Exception\NotDeletableException;

/**
 * Entity class to represent a Person.
 *
 * @ORM\Entity
 *
 * @Assert\GroupSequence({
 *     "id",
 *     "unique_id",
 *     "Person",
 *     "Entity",
 * })
 *
 * @UniqueEntity(
 *     fields={"emailAddress"},
 *     errorPath="emailAddress",
 *     message="A Person with this email address already exists"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_people_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_people_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_people_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class Person extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person';

    /**
     * Person's first name.
     *
     * @var string $firstName
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="First name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="First name cannot contain angle brackets (< or >)"
     * )
     */
    protected $firstName;

    /**
     * Person's last name.
     *
     * @var string $lastName
     *
     * @ORM\Column(type="citext")
     *
     * @Assert\NotBlank(
     *     message="Last name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     */
    protected $lastName;

    /**
     * Person's email address.
     *
     * @var string $emailAddress
     *
     * @ORM\Column(type="citext", unique=true)
     *
     * @Assert\NotBlank(
     *     message="Email address is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid",
     *     strict=true
     * )
     */
    protected $emailAddress;

    /**
     * Person's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * Person's delivery point (street address).
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Person's city.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Person's administrative area (state).
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Person's postal code (zipcode).
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Person's country.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Person's Website url.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Person's organization.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Organization cannot contain angle brackets (< or >)"
     * )
     */
    protected $organization;

    /**
     * Person's position.
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Position cannot contain angle brackets (< or >)"
     * )
     */
    protected $position;

    /**
     * Person's PersonFundingOrganizations.
     *
     * @var Collection $personFundingOrganizations
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="PersonFundingOrganization", mappedBy="person")
     */
    protected $personFundingOrganizations;

    /**
     * Person's PersonResearchGroups.
     *
     * @var Collection $personResearchGroups
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="PersonResearchGroup", mappedBy="person")
     */
    protected $personResearchGroups;

    /**
     * Person's PersonDataRepositories.
     *
     * @var Collection $personDataRepositories
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="PersonDataRepository", mappedBy="person")
     */
    protected $personDataRepositories;

    /**
     * Person's Account.
     *
     * @var Account $account
     *
     * @access protected
     *
     * @ORM\OneToOne(targetEntity="Account", mappedBy="person")
     *
     * @Serializer\Exclude
     */
    protected $account;

    /**
     * Person's Token.
     *
     * @var PersonToken $token
     *
     * @access protected
     *
     * @ORM\OneToOne(targetEntity="PersonToken", mappedBy="person")
     *
     * @Serializer\Exclude
     */
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
     * @param string $phoneNumber Person's phone number.
     *
     * @access public
     *
     * @return void
     */
    public function setPhoneNumber(string $phoneNumber)
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
     * @param string $deliveryPoint Street address of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setDeliveryPoint(string $deliveryPoint)
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
     * @param string $city City of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setCity(string $city)
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
     * @param string $administrativeArea Person's administrative area (state).
     *
     * @access public
     *
     * @return void
     */
    public function setAdministrativeArea(string $administrativeArea)
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
     * @param string $postalCode Postal (zip) code.
     *
     * @access public
     *
     * @return void
     */
    public function setPostalCode(string $postalCode)
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
     * @param string $country Person's country.
     *
     * @access public
     *
     * @return void
     */
    public function setCountry(string $country)
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
     * @param string $url Person's Website URL.
     *
     * @access public
     *
     * @return void
     */
    public function setUrl(string $url)
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
     * @param string $organization Person's organization.
     *
     * @access public
     *
     * @return void
     */
    public function setOrganization(string $organization)
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
     * @param string $position Person's position.
     *
     * @access public
     *
     * @return void
     */
    public function setPosition(string $position)
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
     * Setter for personFundingOrganizations.
     *
     * @param array|\Traversable $personFundingOrganizations Set of PersonFundingOrganization objects.
     *
     * @access public
     *
     * @throws \Exception When $personFundingOrganizations is not an array or traversable object.
     * @throws \Exception When Non-PersonFundingOrganization found in $personFundingOrganizations.
     *
     * @return void
     */
    public function setPersonFundingOrganizations($personFundingOrganizations)
    {
        if (is_array($personFundingOrganizations) || $personFundingOrganizations instanceof \Traversable) {
            foreach ($personFundingOrganizations as $personFundingOrganization) {
                if (!$personFundingOrganization instanceof PersonFundingOrganization) {
                    throw new \Exception('Non-PersonFundingOrganization found in personFundingOrganizations.');
                }
            }
            $this->personFundingOrganizations = $personFundingOrganizations;
            foreach ($this->personFundingOrganizations as $personFundingOrganization) {
                $personFundingOrganization->setPerson($this);
            }
        } else {
            throw new \Exception('personFundingOrganizations must be either array or traversable objects.');
        }
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
     * Setter for personResearchGroups.
     *
     * @param array|\Traversable $personResearchGroups Set of PersonResearchGroup objects.
     *
     * @access public
     *
     * @throws \Exception When Non-PersonResearchGroup found in $personResearchGroups.
     * @throws \Exception When $personResearchGroups is not an array or traversable object.
     *
     * @return void
     */
    public function setPersonResearchGroups($personResearchGroups)
    {
        if (is_array($personResearchGroups) || $personResearchGroups instanceof \Traversable) {
            foreach ($personResearchGroups as $personResearchGroup) {
                if (!$personResearchGroup instanceof PersonResearchGroup) {
                    throw new \Exception('Non-PersonResearchGroup found in personResearchGroups.');
                }
            }
            $this->personResearchGroups = $personResearchGroups;
            foreach ($this->personResearchGroups as $personResearchGroup) {
                $personResearchGroup->setPerson($this);
            }
        } else {
            throw new \Exception('personResearchGroups must be either array or traversable objects.');
        }
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
     * Setter for personDataRepositories.
     *
     * @param array|\Traversable $personDataRepositories Set of PersonDataRepository objects.
     *
     * @access public
     *
     * @throws \Exception When Non-PersonDataRepository found in $personDataRepositories.
     * @throws \Exception When $personDataRepositories is not an array or traversable object.
     *
     * @return void
     */
    public function setPersonDataRepositories($personDataRepositories)
    {
        if (is_array($personDataRepositories) || $personDataRepositories instanceof \Traversable) {
            foreach ($personDataRepositories as $personDataRepository) {
                if (!$personDataRepository instanceof PersonDataRepository) {
                    throw new \Exception('Non-PersonDataRepository found in personDataRepositories.');
                }
            }
            $this->personDataRepositories = $personDataRepositories;
            foreach ($this->personDataRepositories as $personDataRepository) {
                $personDataRepository->setPerson($this);
            }
        } else {
            throw new \Exception('personDataRepositories must be either array or traversable objects.');
        }
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
        $collection = new ArrayCollection;
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

    /**
     * Return the id as a string when converting a Person to a string.
     *
     * This is needed for serialization of the Account entity.
     *
     * @return string String representation of a Person object.
     */
    public function __toString()
    {
        return (string) $this->id;
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
}
