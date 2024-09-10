<?php

namespace App\Entity;

use App\Twig\Extensions as TwigExtentions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use JMS\Serializer\Annotation as Serializer;
use App\Exception\NotDeletableException;
use Symfony\Component\Serializer\Annotation\Groups as SerializerGroups;

/**
 * Entity class to represent a Research Group.
 *
 *
 *
 * @Assert\GroupSequence({
 *     "id",
 *     "unique_id",
 *     "ResearchGroup",
 *     "Entity",
 * })
 *
 * @UniqueEntity(
 *     fields={"name", "fundingCycle"},
 *     errorPath="name",
 *     message="A Research Group with this name already exists"
 * )
 *
 * @UniqueEntity("shortName", message="A Research Group with this Short name already exists")
 */
#[ORM\Entity(repositoryClass: 'App\Repository\ResearchGroupRepository')]
class ResearchGroup extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Research Group';

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
     *
     * @access protected
     *
     * @Serializer\Groups({"overview", "search"})
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    #[SerializerGroups(['export'])]
    #[ORM\Column(type: 'citext', options: ['collation' => 'POSIX'])]
    protected $name;

    /**
     * Short Name of a research group.
     *
     * @var string $shortName
     *
     *
     * @Assert\NotBlank(
     *     message="Short Name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Short name cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'citext', unique: true, nullable: true)]
    protected $shortName;

    /**
     * Research group's parent Funding Cycle.
     *
     * @var FundingCycle $fundingCycle
     *
     * @access protected
     *
     *
     * @Assert\NotBlank(
     *     message="Funding Cycle is required"
     * )
     *
     * @Serializer\MaxDepth(2)
     * @Serializer\Groups({"overview"})
     */
    #[SerializerGroups(['export'])]
    #[ORM\ManyToOne(targetEntity: 'FundingCycle', inversedBy: 'researchGroups')]
    protected $fundingCycle;

    /**
     * Research group's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Research group's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $phoneNumber;

    /**
     * Research group's delivery point (street address).
     *
     * @var string $deliveryPoint
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $deliveryPoint;

    /**
     * Research group's city.
     *
     * @var string $city
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * Research group's administrative area (state).
     *
     * @var string $administrativeArea
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $administrativeArea;

    /**
     * Research group's postal code (zipcode).
     *
     * @var string $postalCode
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postalCode;

    /**
     * Research group's country.
     *
     * @var string $country
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * Description of a research group.
     *
     * @var string $description
     *
     * @access protected
     *
     * @Serializer\Groups({"overview"})
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * Research group's logo.
     *
     * @var string|resource $logo
     *
     * @access protected
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    protected $logo;

    /**
     * Research group's email address.
     *
     * @var string $emailAddress
     *
     * @access protected
     *
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    #[ORM\Column(type: 'citext', nullable: true)]
    protected $emailAddress;

    /**
     * Research group's PersonResearchGroups.
     *
     * @var \Doctrine\Common\Collections\Collection $personResearchGroups
     *
     * @access protected
     *
     *
     * @Serializer\Groups({"overview"})
     */
    #[ORM\OneToMany(targetEntity: 'PersonResearchGroup', mappedBy: 'researchGroup')]
    protected $personResearchGroups;

    /**
     * Research group's list of Datasets.
     *
     * @var Collection $datasets
     *
     *
     *
     * @Serializer\Groups({"overview"})
     */
    #[ORM\OneToMany(targetEntity: 'Dataset', mappedBy: 'researchGroup')]
    #[ORM\OrderBy(['udi' => 'ASC'])]
    protected $datasets;

    /**
     * Lock flag for Research Group, defaults to false for new Research Groups.
     *
     * @var boolean $locked
     *
     * @access public
     *
     * @Serializer\Groups({"data"})
     *
     * @Assert\NotNull(
     *     message="Please select Yes or No"
     * )
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected $locked = false;

    /**
     * Getter for Datasets.
     *
     * @return Collection A Collection of Datasets.
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * Serializer for the datasets virtual property.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("datasets")
     *
     * @return array
     */
    public function serializeDatasets()
    {
        $datasets = array();
        foreach ($this->datasets as $dataset) {
            $datasetArray = array(
                'id' => $dataset->getId(),
                'title' => $dataset->getTitle(),
                'udi' => $dataset->getUdi(),
                'availabilityStatus' => $dataset->getAvailabilityStatus(),
                'doi' => array(
                    'doi' => ($dataset->getDoi()) ? $dataset->getDoi()->getDoi() : ''
                ),
                'acceptedDate' => ($dataset->getAcceptedDate()) ? $dataset->getAcceptedDate()->format('Y-m-d') : ''
            );
            if (null !== $dataset->getDif()) {
                $datasetArray['dif'] = array(
                    'id' => $dataset->getDif()->getId(),
                    'status' => $dataset->getDif()->getStatus(),
                    'title' => $dataset->getDif()->getTitle(),
                );
            } else {
                $datasetArray['dif'] = null;
            }
            if ($dataset->hasDatasetSubmission()) {
                $datasetArray['datasetSubmission'] = array(
                    'authors' =>  $dataset->getDatasetSubmission()->getAuthors(),
                    'themeKeywords' => $dataset->getDatasetSubmission()->getThemeKeywords()
                );
                $datasetArray['fileFormat'] = $dataset->getDatasetSubmission()->getDistributionFormatName();
                if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                    $datasetArray['fileSize'] = TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileColdStorageArchiveSize(), 2);
                } else {
                    $datasetArray['fileSize'] = TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileSize(), 2);
                }
            } else {
                $datasetArray['datasetSubmission'] = null;
            }
            $datasetArray['publications'] = array();
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
     * @param string $name Textual name of research group.
     *
     * @access public
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
     * @access public
     *
     * @return string String containing name of research group.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for fundingCycle.
     *
     * @param FundingCycle $fundingCycle The FundingCycle to associate this ResearchGroup with.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingCycle(FundingCycle $fundingCycle = null)
    {
        $this->fundingCycle = $fundingCycle;
    }

    /**
     * Getter for fundingCycles.
     *
     * @access public
     *
     * @return FundingCycle String containing fundingCycles of research group.
     */
    public function getFundingCycle()
    {
        return $this->fundingCycle;
    }

    /**
     * Setter for url.
     *
     * @param string|null $url Research group's Website URL.
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
     * @return string URL of research group's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string|null $phoneNumber Research group's phone number.
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
     * @return string Phone number of research group.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string|null $deliveryPoint Street address of research group.
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
     * @return string Street address of research group.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string|null $city City of research group.
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
     * @return string City of research group.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string|null $administrativeArea Research group's administrative area (state).
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
     * @return string Research group's administrative area (state).
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
     * @param string|null $country Research group's country.
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
     * @return string Research group's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description Description of research group.
     *
     * @access public
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
     * @access public
     *
     * @return string Description of research group.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for logo.
     *
     * @param string|resource $logo Containing byte string of logo.
     *
     * @access public
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
     * @param boolean $asStream Whether to return the logo as a stream.
     *
     * @access public
     *
     * @return string|resource Binary string containing the logo or a stream resource pointing to it.
     */
    public function getLogo(bool $asStream = false)
    {
        if ($asStream) {
            if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
                return $this->logo;
            } else {
                return null;
            }
        }
        if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
            rewind($this->logo);
            return stream_get_contents($this->logo);
        }
        return $this->logo;
    }

    /**
     * Setter for emailAddress.
     *
     * @param string|null $emailAddress Containing email address of research group.
     *
     * @access public
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
     * @access public
     *
     * @return string Containing emailAddress.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Method to check if Research Group is locked.
     *
     * @access public
     *
     * @return boolean Set to true if this Research Group is locked, false otherwise.
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Setter for locked.
     *
     * @param boolean $locked Containing desired state of lock.
     *
     * @access public
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
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personResearchGroups
     *                                                 listings for this research group.
     */
    public function getPersonResearchGroups()
    {
        return $this->personResearchGroups;
    }

    /**
     * Check if this ResearchGroup is deletable.
     *
     * This method throws a NotDeletableException when the ResearchGroup has associated
     * Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the ResearchGroup is not deletable.
     *
     * @throws NotDeletableException When the ResearchGroup has associated Persons.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
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
     * @param ResearchGroup $a First Research Group to compare.
     * @param ResearchGroup $b Second Research Group to compare.
     *
     * @return integer
     */
    public static function compareByName(ResearchGroup $a, ResearchGroup $b)
    {
        return strcmp($a->getName(), $b->getName());
    }

    /**
     * Getter for short name.
     *
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * Setter for short name.
     *
     * @param string|null $shortName Short name for the research group.
     *
     * @return void
     */
    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * Returns a collection of project directors (Person entity).
     *
     * @Serializer\Groups({"overview"})
     * @Serializer\VirtualProperty
     *
     * @return Collection
     */
    #[SerializerGroups(['export'])]
    public function getProjectDirectors(): Collection
    {
        $projectDirectors = new ArrayCollection();
        foreach ($this->getPersonResearchGroups() as $personResearchGroup) {
            if ($personResearchGroup->getRole()->getName() === ResearchGroupRole::LEADERSHIP) {
                $projectDirectors->add($personResearchGroup->getPerson());
            }
        }
        return $projectDirectors;
    }
}
