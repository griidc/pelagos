<?php

namespace App\Entity;

use App\Enum\DatasetLifecycleStatus;
use App\Exception\NotDeletableException;
use App\Repository\FundingOrganizationRepository;
use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity class to represent a Funding Organization.
 */
#[ORM\Entity(repositoryClass: FundingOrganizationRepository::class)]
#[UniqueEntity(fields: ['name'], errorPath: 'name', message: 'A Funding Organization with this name already exists')]
#[UniqueEntity('shortName', message: 'A Funding Organization with this Short name already exists')]
class FundingOrganization extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Funding Organization';

    /**
     * Name of a funding organization.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', unique: true)]
    #[Serializer\Groups(['organization'])]
    #[Assert\NotBlank(message: 'Name is required')]
    protected $name;

    /**
     * Short Name of a funding organization.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Short name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', unique: true, nullable: true)]
    protected $shortName;

    /**
     * Funding organization's logo.
     *
     * @var string|resource $logo
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    protected $logo;

    /**
     * Funding organization's email address.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Email address cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Email(message: 'Email address is invalid')]
    protected $emailAddress;

    /**
     * Description of a funding organization.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Description cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * Funding organization's Website url.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Website URL cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['organization'])]
    protected $url;

    /**
     * Funding organization's telephone number.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Phone number cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $phoneNumber;

    /**
     * Funding organization's delivery point (street address).
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Delievery point (address) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $deliveryPoint;

    /**
     * Funding organization's city.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'City cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * Funding organization's administrative area (state).
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Administrative area (state) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $administrativeArea;

    /**
     * Funding organization's postal code (zipcode).
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Postal code (zip) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postalCode;

    /**
     * Funding organization's country.
     *
     * @var string
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Country cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * Funding organization's Funding Cycle's.
     *
     * @var FundingCycle[]
     */
    #[ORM\OneToMany(targetEntity: 'FundingCycle', mappedBy: 'fundingOrganization')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    protected $fundingCycles;

    /**
     * Funding Organization's PersonFundingOrganizations.
     *
     * @var Collection $personFundingOrganizations
     */
    #[ORM\OneToMany(targetEntity: PersonFundingOrganization::class, mappedBy: 'fundingOrganization', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $personFundingOrganizations;

    /**
     * This FundingOrganization's parent DataRepository.
     *
     * @var DataRepository
     */
    #[ORM\ManyToOne(targetEntity: 'DataRepository', inversedBy: 'fundingOrganizations')]
    #[Assert\NotBlank(message: 'Data Repository is required')]
    protected $dataRepository;

    /**
     * This holds the position in the sort order of this Entity.
     *
     * @var int
     */
    #[ORM\Column(nullable: true, type: 'integer')]
    #[Assert\Range(min: 1, max: 2147483647, notInRangeMessage: 'Sort position must be in between 1 and 2147483647', invalidMessage: 'Sort position must be a positive integer.')]
    protected $sortOrder;

    /**
     * The default funder for this Funding Organization.
     *
     * @var ?Funder
     */
    #[ORM\ManyToOne(targetEntity: Funder::class)]
    #[Assert\NotBlank(message: 'Default Funder is required')]
    protected $defaultFunder;

    /**
     * Getter for fundingCycles.
     *
     * @return FundingCycle[] containing fundingCycles of funding organization
     */
    public function getFundingCycles()
    {
        return $this->fundingCycles;
    }

    /**
     * Return a collection of all Datasets for the Funding Organization.
     */
    public function getDatasets(): Collection
    {
        $datasets = new ArrayCollection();
        foreach ($this->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                /** @var ResearchGroup $researchGroup */
                foreach ($researchGroup->getDatasets() as $dataset) {
                    $datasets->add($dataset);
                }
            }
        }

        return $datasets;
    }

    /**
     * Return the research groups associated with this Funding Organization.
     */
    public function getResearchGroups(): Collection
    {
        $researchGroups = new ArrayCollection();
        foreach ($this->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                $researchGroups->add($researchGroup);
            }
        }

        return $researchGroups;
    }

    /**
     * Returns datasets by Dataset Lifecycle Status.
     */
    public function getDatasetsByLifecycleStatus(DatasetLifecycleStatus $datasetLifecycleStatus): Collection
    {
        return $this->getDatasets()->filter(function (Dataset $dataset) use ($datasetLifecycleStatus) {
            return $dataset->getDatasetLifecycleStatus() === $datasetLifecycleStatus;
        });
    }

    /**
     * Setter for fundingCycles.
     *
     * @param array|\Traversable $fundingCycles set of FundingCycle objects
     *
     * @return void
     *
     * @throws \Exception when Non-funding cycle found in $fundingCycles
     * @throws \Exception when $fundingCycles is not an array or traversable object
     */
    public function setFundingCycles($fundingCycles)
    {
        if (is_array($fundingCycles) || $fundingCycles instanceof \Traversable) {
            $this->fundingCycles = $fundingCycles;
            foreach ($fundingCycles as $fundingCycle) {
                if (!$fundingCycle instanceof FundingCycle) {
                    throw new \Exception('Non-funding cycle found in FundingCycles');
                }
                $fundingCycle->setFundingOrganization($this);
            }
        } else {
            throw new \Exception('Funding Cycles must be array or traversable objects');
        }
    }

    /**
     * Setter for name.
     *
     * @param string $name textual name of funding organization
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name.
     *
     * @return string string containing name of funding organization
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for logo.
     *
     * @param string|resource $logo containing byte string of logo
     *
     * @return void
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Getter for logo.
     *
     * @param bool $asStream whether to return the logo as a stream
     *
     * @return string|resource binary string containing the logo or a stream resource pointing to it
     */
    public function getLogo(bool $asStream = false)
    {
        if ($asStream) {
            if (is_resource($this->logo) and 'stream' == get_resource_type($this->logo)) {
                return $this->logo;
            } else {
                return null;
            }
        }
        if (is_resource($this->logo) and 'stream' == get_resource_type($this->logo)) {
            rewind($this->logo);

            return stream_get_contents($this->logo);
        }

        return $this->logo;
    }

    /**
     * Get the mime type of logo.
     *
     * @return string the mime type of logo
     */
    public function getLogoMimeType()
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->buffer($this->getLogo());
    }

    /**
     * Setter for emailAddress.
     *
     * @param string|null $emailAddress containing email address of funding organization
     *
     * @return void
     */
    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress.
     *
     * @return string containing emailADdress
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description description of funding organization
     *
     * @return void
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    /**
     * Getter for description.
     *
     * @return string description of funding organization
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for url.
     *
     * @param string|null $url funding organization's Website URL
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
     * @return string URL of funding organization's Website
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for sortOrder.
     *
     * @param int|null $position the position to set in the sort ordering
     *
     * @return void
     *
     * @throws \InvalidArgumentException if parameter passed is neither integer or null
     */
    public function setSortOrder(?int $position)
    {
        $this->sortOrder = $position;
    }

    /**
     * Getter for sortOrder.
     *
     * @return int of position to use in a sorted list
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string|null $phoneNumber funding organization's phone number
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
     * @return string phone number of funding organization
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string|null $deliveryPoint street address of funding organization
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
     * @return string street address of funding organization
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string|null $city city of funding organization
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
     * @return string city of funding organization
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string|null $administrativeArea funding organization's administrative area (state)
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
     * @return string funding organization's administrative area (state)
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for postalCode.
     *
     * @param string|null $postalCode postal (zip) code
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
     * @return string containing postal (zip) code
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for country.
     *
     * @param string|null $country funding organization's country
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
     * @return string funding organization's country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for personFundingOrganizations.
     *
     * @param array|\Traversable $personFundingOrganizations set of PersonFundingOrganization objects
     *
     * @return void
     *
     * @throws \Exception when $personFundingOrganizations is not an array or traversable object
     * @throws \Exception when Non-PersonFundingOrganization found within $personFundingOrganizations
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
                $personFundingOrganization->setFundingOrganization($this);
            }
        } else {
            throw new \Exception('personFundingOrganizations must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personFundingOrganizations.
     *
     * @return \Doctrine\Common\Collections\Collection collection containing personFundingOrganizations
     *                                                 listings for this Funding Organization
     */
    public function getPersonFundingOrganizations()
    {
        return $this->personFundingOrganizations;
    }

    /**
     * Get People from Research Groups associated with this Funding Organization.
     */
    public function getPeople(): Collection
    {
        $people = new ArrayCollection();
        foreach ($this->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                /** @var ResearchGroup $researchGroup */
                foreach ($researchGroup->getPeople() as $person) {
                    if (!$people->contains($person)) {
                        $people->add($person);
                    }
                }
            }
        }

        return $people;
    }

    /**
     * Setter for dataRepository.
     *
     * @param DataRepository $dataRepository an instance of \App\Entity\DataRepository
     *
     * @return void
     */
    public function setDataRepository(DataRepository $dataRepository = null)
    {
        $this->dataRepository = $dataRepository;
        if (null !== $dataRepository) {
            $this->dataRepository->addFundingOrganization($this);
        }
    }

    /**
     * Getter for dataRepository.
     *
     * @return DataRepository instance representing the parent data repository
     */
    public function getDataRepository()
    {
        return $this->dataRepository;
    }

    /**
     * Check if this FundingOrganization is deletable.
     *
     * This method throws a NotDeletableException when the FundingOrganization has associated
     * FundingCycles or Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the FundingOrganization is not deletable.
     *
     * @return void
     *
     * @throws NotDeletableException when the FundingOrganization has associated FundingCycles or Persons
     */
    public function checkDeletable()
    {
        $notDeletableReasons = [];
        $fundingCycleCount = count($this->getFundingCycles());
        if ($fundingCycleCount > 0) {
            $notDeletableReasons[] = 'there ' . ($fundingCycleCount > 1 ? 'are' : 'is') .
                " $fundingCycleCount associated Funding Cycle" .
                ($fundingCycleCount > 1 ? 's' : '');
        }
        $personFundingOrganizationCount = count($this->getPersonFundingOrganizations());
        if ($personFundingOrganizationCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personFundingOrganizationCount > 1 ? 'are' : 'is') .
                " $personFundingOrganizationCount associated Person" .
                ($personFundingOrganizationCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }

    /**
     * Getter for short name.
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * Setter for short name.
     *
     * @param string|null $shortName short name for the funding organization
     */
    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * Get the default funder for this Funding Organization.
     */
    public function getDefaultFunder(): ?Funder
    {
        return $this->defaultFunder;
    }

    /**
     * Sets the default funder for this Funding Organization.
     *
     * @return void
     */
    public function setDefaultFunder(?Funder $defaultFunder)
    {
        $this->defaultFunder = $defaultFunder;
    }

    /**
     * The name of this Funding Organization.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
