<?php

namespace App\Entity;

use App\Enum\DatasetLifecycleStatus;
use App\Exception\NotDeletableException;
use App\Repository\ResearchGroupRepository;
use App\Twig\Extensions as TwigExtentions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity class to represent a Research Group.
 */
#[ORM\Entity(repositoryClass: ResearchGroupRepository::class)]
#[Assert\GroupSequence(['id', 'unique_id', 'ResearchGroup', 'Entity'])]
#[UniqueEntity(fields: ['name', 'fundingCycle'], errorPath: 'name', message: 'A Research Group with this name already exists')]
#[UniqueEntity('shortName', message: 'A Research Group with this Short name already exists')]
class ResearchGroup extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Research Group';

    /**
     * Highest acceptable ID number.
     */
    public const MAX_ID = 999;

    /**
     * Minimum acceptable ID number.
     */
    public const MIN_ID = 1;

    /**
     * Name of a research group.
     *
     * @var string $name
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', options: ['collation' => 'POSIX'])]
    #[Serializer\Groups(['overview', 'search'])]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Groups(['grp-dp-report', 'grp-dk-report', 'grp-people-accounts-report'])]
    #[SerializedName('researchGroupName')]
    protected $name;

    /**
     * Short Name of a research group.
     *
     * @var string $shortName
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Short name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', unique: true, nullable: true)]
    #[Assert\NotBlank(message: 'Short Name is required')]
    protected $shortName;

    /**
     * Research group's parent Funding Cycle.
     *
     * @var FundingCycle $fundingCycle
     */
    #[ORM\ManyToOne(targetEntity: 'FundingCycle', inversedBy: 'researchGroups', fetch: 'EAGER')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Serializer\MaxDepth(2)]
    #[Serializer\Groups(['overview'])]
    #[Assert\NotBlank(message: 'Funding Cycle is required')]
    protected $fundingCycle;

    /**
     * Research group's Website url.
     *
     * @var string $url
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Website URL cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Research group's telephone number.
     *
     * @var string $phoneNumber
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Phone number cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $phoneNumber;

    /**
     * Research group's delivery point (street address).
     *
     * @var string $deliveryPoint
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Delievery point (address) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $deliveryPoint;

    /**
     * Research group's city.
     *
     * @var string $city
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'City cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * Research group's administrative area (state).
     *
     * @var string $administrativeArea
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Administrative area (state) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $administrativeArea;

    /**
     * Research group's postal code (zipcode).
     *
     * @var string $postalCode
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Postal code (zip) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postalCode;

    /**
     * Research group's country.
     *
     * @var string $country
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Country cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * Description of a research group.
     *
     * @var string $description
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Description cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['overview'])]
    protected $description;

    /**
     * Research group's logo.
     *
     * @var string|resource $logo
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    protected $logo;

    /**
     * Research group's email address.
     *
     * @var string $emailAddress
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Email address cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext', nullable: true)]
    #[Assert\Email(message: 'Email address is invalid')]
    protected $emailAddress;

    /**
     * Research group's PersonResearchGroups.
     *
     * @var Collection $personResearchGroups
     */
    #[ORM\OneToMany(targetEntity: 'PersonResearchGroup', mappedBy: 'researchGroup')]
    #[Serializer\Groups(['overview'])]
    protected $personResearchGroups;

    /**
     * Research group's list of Datasets.
     *
     * @var Collection $datasets
     */
    #[ORM\OneToMany(targetEntity: 'Dataset', mappedBy: 'researchGroup')]
    #[ORM\OrderBy(['udi' => 'ASC'])]
    #[Serializer\Groups(['overview'])]
    protected $datasets;

    /**
     * Lock flag for Research Group, defaults to false for new Research Groups.
     *
     * @var bool $locked
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Serializer\Groups(['data'])]
    #[Assert\NotNull(message: 'Please select Yes or No')]
    protected $locked = false;

    /**
     * Getter for Datasets.
     *
     * @return Collection a Collection of Datasets
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * Serializer for the datasets virtual property.
     *
     * @return array
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('datasets')]
    public function serializeDatasets()
    {
        $datasets = [];
        foreach ($this->datasets as $dataset) {
            $datasetArray = [
                'id' => $dataset->getId(),
                'title' => $dataset->getTitle(),
                'udi' => $dataset->getUdi(),
                'availabilityStatus' => $dataset->getAvailabilityStatus(),
                'doi' => [
                    'doi' => ($dataset->getDoi()) ? $dataset->getDoi()->getDoi() : '',
                ],
                'acceptedDate' => ($dataset->getAcceptedDate()) ? $dataset->getAcceptedDate()->format('Y-m-d') : '',
            ];
            if (null !== $dataset->getDif()) {
                $datasetArray['dif'] = [
                    'id' => $dataset->getDif()->getId(),
                    'status' => $dataset->getDif()->getStatus(),
                    'title' => $dataset->getDif()->getTitle(),
                ];
            } else {
                $datasetArray['dif'] = null;
            }
            if ($dataset->hasDatasetSubmission()) {
                $datasetArray['datasetSubmission'] = [
                    'authors' => $dataset->getDatasetSubmission()->getAuthors(),
                    'themeKeywords' => $dataset->getDatasetSubmission()->getThemeKeywords(),
                ];
                $datasetArray['fileFormat'] = $dataset->getDatasetSubmission()->getDistributionFormatName();
                if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                    $datasetArray['fileSize'] = TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileColdStorageArchiveSize(), 2);
                } else {
                    $datasetArray['fileSize'] = TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileSize(), 2);
                }
            } else {
                $datasetArray['datasetSubmission'] = null;
            }
            $datasetArray['publications'] = [];
            foreach ($dataset->getDatasetPublications() as $datasetPublication) {
                array_push($datasetArray['publications'], $datasetPublication->getPublication());
            }

            $datasets[] = $datasetArray;
        }
        $array_column = array_column($datasets, 'id');
        array_multisort($array_column, SORT_DESC, $datasets);

        return $datasets;
    }

    /**
     * Setter for name.
     *
     * @param string $name textual name of research group
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
     * @return string string containing name of research group
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for fundingCycle.
     *
     * @param FundingCycle $fundingCycle the FundingCycle to associate this ResearchGroup with
     *
     * @return void
     */
    public function setFundingCycle(?FundingCycle $fundingCycle = null)
    {
        $this->fundingCycle = $fundingCycle;
    }

    /**
     * Getter for fundingCycles.
     *
     * @return FundingCycle string containing fundingCycles of research group
     */
    public function getFundingCycle()
    {
        return $this->fundingCycle;
    }

    /**
     * Get the name of the Funding Cycle for this Research Group.
     */
    #[Groups(['grp-dp-report', 'grp-dk-report', 'grp-people-accounts-report'])]
    public function getFundingCycleName(): string
    {
        return $this->fundingCycle->getName();
    }

    /**
     * Setter for url.
     *
     * @param string|null $url research group's Website URL
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
     * @return string URL of research group's Website
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string|null $phoneNumber research group's phone number
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
     * @return string phone number of research group
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string|null $deliveryPoint street address of research group
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
     * @return string street address of research group
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string|null $city city of research group
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
     * @return string city of research group
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string|null $administrativeArea research group's administrative area (state)
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
     * @return string research group's administrative area (state)
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
     * @param string|null $country research group's country
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
     * @return string research group's country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description description of research group
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
     * @return string description of research group
     */
    public function getDescription()
    {
        return $this->description;
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
     * Setter for emailAddress.
     *
     * @param string|null $emailAddress containing email address of research group
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
     * @return string containing emailAddress
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Method to check if Research Group is locked.
     *
     * @return bool set to true if this Research Group is locked, false otherwise
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Setter for locked.
     *
     * @param bool $locked containing desired state of lock
     *
     * @return void
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    /**
     * Setter for personResearchGroups.
     *
     * @param array|\Traversable $personResearchGroups set of PersonResearchGroup objects
     *
     * @return void
     *
     * @throws \Exception when Non-PersonResearchGroup found in $personResearchGroups
     * @throws \Exception when $personResearchGroups is not an array or traversable object
     */
    public function setPersonResearchGroups($personResearchGroups)
    {
        if (is_array($personResearchGroups) || $personResearchGroups instanceof \Traversable) {
            $this->personResearchGroups = $personResearchGroups;
            foreach ($personResearchGroups as $personResearchGroup) {
                if (!$personResearchGroup instanceof PersonResearchGroup) {
                    throw new \Exception('Non-PersonResearchGroup found in personResearchGroups.');
                }
                $personResearchGroup->setResearchGroup($this);
            }
        } else {
            throw new \Exception('personResearchGroups must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personResearchGroups.
     *
     * @return Collection collection containing personResearchGroups
     *                    listings for this research group
     */
    public function getPersonResearchGroups()
    {
        return $this->personResearchGroups;
    }

    /**
     * Get People for this Research Group.
     */
    public function getPeople(): Collection
    {
        $people = new ArrayCollection();
        foreach ($this->getPersonResearchGroups() as $personResearchGroup) {
            $person = $personResearchGroup->getPerson();
            if (!$people->contains($person)) {
                $people->add($person);
            }
        }

        return $people;
    }

    /**
     * Return a count of People for this Research Group.
     */
    #[Groups(['grp-dp-report'])]
    public function getPeopleCount(): int
    {
        return $this->getPeople()->count();
    }

    /**
     * Get publications for this Research Group.
     */
    public function getPublications(): Collection
    {
        $publications = new ArrayCollection();
        foreach ($this->getDatasets() as $dataset) {
            foreach ($dataset->getDatasetPublications() as $datasetPublication) {
                foreach ($dataset->getPublications() as $publication) {
                    if (!$publications->contains($publication)) {
                        $publications->add($publication);
                    }
                }
            }
        }

        return $publications;
    }

    /**
     * Check if this ResearchGroup is deletable.
     *
     * This method throws a NotDeletableException when the ResearchGroup has associated
     * Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the ResearchGroup is not deletable.
     *
     * @return void
     *
     * @throws NotDeletableException when the ResearchGroup has associated Persons
     */
    public function checkDeletable()
    {
        $notDeletableReasons = [];
        $personResearchGroupCount = count($this->getPersonResearchGroups());
        if ($personResearchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personResearchGroupCount > 1 ? 'are' : 'is') .
                " $personResearchGroupCount associated Person" .
                ($personResearchGroupCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }

    /**
     * Compare two Research Groups by name.
     *
     * @param ResearchGroup $a first Research Group to compare
     * @param ResearchGroup $b second Research Group to compare
     *
     * @return int
     */
    public static function compareByName(ResearchGroup $a, ResearchGroup $b)
    {
        return strcmp($a->getName(), $b->getName());
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
     * @param string|null $shortName short name for the research group
     */
    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * Returns a collection of project directors (Person entity).
     */
    #[Serializer\Groups(['overview'])]
    #[Serializer\VirtualProperty]
    public function getProjectDirectors(): Collection
    {
        $projectDirectors = new ArrayCollection();
        foreach ($this->getPersonResearchGroups() as $personResearchGroup) {
            if (ResearchGroupRole::LEADERSHIP === $personResearchGroup->getRole()->getName()) {
                $projectDirectors->add($personResearchGroup->getPerson());
            }
        }

        return $projectDirectors;
    }

    /**
     * Returns datasets by Dataset Lifecycle Status.
     */
    public function getDatasetsByLifecycleStatus(DatasetLifecycleStatus $datasetLifecycleStatus): Collection
    {
        return $this->datasets->filter(function (Dataset $dataset) use ($datasetLifecycleStatus) {
            return $dataset->getDatasetLifecycleStatus() === $datasetLifecycleStatus;
        });
    }

    /**
     * Return a count of identified datasets.
     */
    #[Groups(['grp-dp-report'])]
    public function getApprovedDifsCount(): int
    {
        return $this->getDatasetsByLifecycleStatus(DatasetLifecycleStatus::IDENTIFIED)->count();
    }

    /**
     * Returns a count of Submitted datasets.
     */
    #[Groups(['grp-dp-report'])]
    public function getSubmittedDatasets(): int
    {
        return $this->getDatasetsByLifecycleStatus(DatasetLifecycleStatus::SUBMITTED)->count();
    }

    /**
     * Returns a count of Available datasets.
     */
    #[Groups(['grp-dp-report'])]
    public function getAvailableDatasets(): int
    {
        return $this->getDatasetsByLifecycleStatus(DatasetLifecycleStatus::AVAILABLE)->count();
    }

    /**
     * Returns a count of Restricted datasets.
     */
    #[Groups(['grp-dp-report'])]
    public function getRestrictedDataset(): int
    {
        return $this->getDatasetsByLifecycleStatus(DatasetLifecycleStatus::RESTRICTED)->count();
    }
}
