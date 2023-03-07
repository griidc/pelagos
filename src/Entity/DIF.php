<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DIF Entity class.
 */
#[ORM\Entity]
class DIF extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'DIF';

    /**
     * Status value for a DIF that has been saved, but not submitted.
     */
    public const STATUS_UNSUBMITTED = 0;

    /**
     * Status value for a DIF that has been submitted, but not yet approved.
     */
    public const STATUS_SUBMITTED = 1;

    /**
     * Status value for a DIF that has been approved.
     */
    public const STATUS_APPROVED = 2;

    /**
     * Valid data sizes for $dataSize.
     */
    public const DATA_SIZES = ['< 1GB', '1GB-10GB', '10GB-200GB', '200GB-1TB', '1TB-5TB', '>5TB'];

    /**
     * Valid values for $ethicalIssues.
     */
    public const ETHICAL_ISSUES = ['No', 'Yes', 'Uncertain'];

    /**
     * The Dataset this DIF identifies.
     *
     * @var Dataset
     *
     * @Serializer\MaxDepth(2)
     */
    #[ORM\OneToOne(targetEntity: 'Dataset', mappedBy: 'dif', cascade: ['persist'])]
    protected $dataset;

    /**
     * The status of this DIF.
     *
     * @var int
     *
     * @see STATUS_* constants.
     *
     *
     * @Serializer\Groups({"card"})
     */
    #[ORM\Column(type: 'smallint')]
    protected $status = self::STATUS_UNSUBMITTED;

    /**
     * The title for this DIF.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Title is required"
     * )
     */
    #[ORM\Column(type: 'text')]
    protected $title;

    /**
     * The primary point of contact for this DIF.
     *
     * @var Person
     *
     *
     * @Assert\NotBlank(
     *     message="Primary Point of Contact is required"
     * )
     * @Serializer\MaxDepth(1)
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    protected $primaryPointOfContact;

    /**
     * The secondary point of contact for this DIF.
     *
     * @var Person
     *
     *
     * @Serializer\MaxDepth(1)
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    protected $secondaryPointOfContact;

    /**
     * The abstract for this DIF.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Abstract is required"
     * )
     */
    #[ORM\Column(type: 'text')]
    protected $abstract;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyEcologicalBiological;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyPhysicalOceanography;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyAtmospheric;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyChemical;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyHumanHealth;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudySocialCulturalPolitical;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $fieldOfStudyEconomics;

    /**
     * Other fields of study for the dataset identified by this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $fieldOfStudyOther;

    /**
     * The approximate data size for the dataset identified by this DIF.
     *
     * @var string
     *
     * @see DATA_SIZES class constant for valid values.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $dataSize;

    /**
     * The Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $variablesObserved;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $collectionMethodFieldSampling;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $collectionMethodSimulatedGenerated;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $collectionMethodLaboratory;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $collectionMethodLiteratureBased;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $collectionMethodRemoteSensing;

    /**
     * Other methods for collecting or generating the dataset identified by this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $collectionMethodOther;

    /**
     * The start date of the estimated data sampling/generation period.
     *
     * @var \DateTime
     *
     *
     * @Serializer\Type("DateTime<'Y-m-d'>")
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $estimatedStartDate;

    /**
     * The end date of the estimated data sampling/generation period.
     *
     * @var \DateTime
     *
     *
     * @Serializer\Type("DateTime<'Y-m-d'>")
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $estimatedEndDate;

    /**
     * A text description of the spatial extent.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $spatialExtentDescription;

    /**
     * The geometry for the spatial extent.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $spatialExtentGeometry;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalDataArchiveNODC;

    /**
     * Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalDataArchiveStoret;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalDataArchiveGBIF;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalDataArchiveNCBI;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $nationalDataArchiveDataGov;

    /**
     * Other national data archives for the dataset identified by this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $nationalDataArchiveOther;

    /**
     * Whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @var string
     *
     * @see ETHICAL_ISSUES class constant for valid values;
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $ethicalIssues;

    /**
     * An explanation of ethical issues for the dataset identified by this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $ethicalIssuesExplanation;

    /**
     * Additional remarks for this DIF.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $remarks;

    /**
     * Approved Date Timestamp for DIF.
     *
     * @var \DateTime $approvedDate;
     */
    #[ORM\Column(type: 'datetimetz', nullable: true)]
    protected $approvedDate;

    /**
     * Additional funders for this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionalFunders;

    /**
     * Constructor.
     *
     * Initializes status to unsubmitted.
     *
     * @param Dataset $dataset the dataset this DIF identifies
     */
    public function __construct(Dataset $dataset = null)
    {
        if (null !== $dataset) {
            $this->setDataset($dataset);
        }
    }

    /**
     * Sets the Dataset this DIF identifies.
     *
     * @param Dataset|null $dataset the Dataset this DIF identifies
     *
     * @return void
     */
    public function setDataset(Dataset $dataset = null)
    {
        $this->dataset = $dataset;
        if (null !== $dataset and $this->dataset->getDif() !== $this) {
            $this->dataset->setDif($this);
        }
        $this->updateIdentifiedStatus();
    }

    /**
     * Gets the Dataset this DIF identifies.
     *
     * @return ?Dataset the Dataset this DIF identifies
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * Returns the status of this DIF.
     *
     * @see STATUS_* constants.
     *
     * @return int the status of this DIF
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the Research Group this DIF is attached to.
     *
     * @param ResearchGroup|null $researchGroup the Research Group this DIF is attached to
     *
     * @return void
     *
     * @throws \Exception when the DIF does not yet have a Dataset
     */
    public function setResearchGroup(ResearchGroup $researchGroup = null)
    {
        if (!$this->dataset instanceof Dataset) {
            throw new \Exception('You must set a dataset before you can set a Research Group');
        }
        $this->dataset->setResearchGroup($researchGroup);
    }

    /**
     * Gets the Research Group this DIF is attached to.
     *
     * @Assert\NotBlank(
     *     message="You must select a project"
     * )
     *
     * @return ResearchGroup the Research Group this DIF is attached to
     */
    public function getResearchGroup()
    {
        if (!$this->dataset instanceof Dataset) {
            return null;
        }

        return $this->dataset->getResearchGroup();
    }

    /**
     * Sets the title for this DIF.
     *
     * @param string $title the title for this DIF
     *
     * @return void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Gets the title for this DIF.
     *
     * @return string the title for this DIF
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the primary point of contact for this DIF.
     *
     * @param Person|null $primaryPointOfContact the primary point of contact for this DIF
     *
     * @return void
     */
    public function setPrimaryPointOfContact(Person $primaryPointOfContact = null)
    {
        $this->primaryPointOfContact = $primaryPointOfContact;
    }

    /**
     * Gets the primary point of contact for this DIF.
     *
     * @return Person the primary point of contact for this DIF
     */
    public function getPrimaryPointOfContact()
    {
        return $this->primaryPointOfContact;
    }

    /**
     * Sets the secondary point of contact for this DIF.
     *
     * @param Person|null $secondaryPointOfContact the secondary point of contact for this DIF
     *
     * @return void
     */
    public function setSecondaryPointOfContact(Person $secondaryPointOfContact = null)
    {
        $this->secondaryPointOfContact = $secondaryPointOfContact;
    }

    /**
     * Gets the secondary point of contact for this DIF.
     *
     * @return Person the secondary point of contact for this DIF
     */
    public function getSecondaryPointOfContact()
    {
        return $this->secondaryPointOfContact;
    }

    /**
     * Sets the abstract for this DIF.
     *
     * @param string $abstract the abstract for this DIF
     *
     * @return void
     */
    public function setAbstract(string $abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Gets the abstract for this DIF.
     *
     * @return string the abstract for this DIF
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @param bool|null $fieldOfStudyEcologicalBiological whether the fields of study for the dataset identified
     *                                                    by this DIF include Ecological/Biological
     *
     * @return void
     */
    public function setFieldOfStudyEcologicalBiological(?bool $fieldOfStudyEcologicalBiological)
    {
        $this->fieldOfStudyEcologicalBiological = $fieldOfStudyEcologicalBiological;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @return bool whether the fields of study for the dataset identified
     *              by this DIF include Ecological/Biological
     */
    public function hasFieldOfStudyEcologicalBiological()
    {
        return $this->fieldOfStudyEcologicalBiological;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @param bool|null $fieldOfStudyPhysicalOceanography whether the fields of study for the dataset identified
     *                                                    by this DIF include Physical Oceanography
     *
     * @return void
     */
    public function setFieldOfStudyPhysicalOceanography(?bool $fieldOfStudyPhysicalOceanography)
    {
        $this->fieldOfStudyPhysicalOceanography = $fieldOfStudyPhysicalOceanography;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @return bool whether the fields of study for the dataset identified
     *              by this DIF include Physical Oceanography
     */
    public function hasFieldOfStudyPhysicalOceanography()
    {
        return $this->fieldOfStudyPhysicalOceanography;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @param bool|null $fieldOfStudyAtmospheric whether the fields of study for the dataset identified
     *                                           by this DIF include Atmospheric
     *
     * @return void
     */
    public function setFieldOfStudyAtmospheric(?bool $fieldOfStudyAtmospheric)
    {
        $this->fieldOfStudyAtmospheric = $fieldOfStudyAtmospheric;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @return bool whether the fields of study for the dataset identified by this DIF include Atmospheric
     */
    public function hasFieldOfStudyAtmospheric()
    {
        return $this->fieldOfStudyAtmospheric;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @param bool|null $fieldOfStudyChemical whether the fields of study for the dataset identified
     *                                        by this DIF include Chemical
     *
     * @return void
     */
    public function setFieldOfStudyChemical(?bool $fieldOfStudyChemical)
    {
        $this->fieldOfStudyChemical = $fieldOfStudyChemical;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @return bool whether the fields of study for the dataset identified by this DIF include Chemical
     */
    public function hasFieldOfStudyChemical()
    {
        return $this->fieldOfStudyChemical;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @param bool|null $fieldOfStudyHumanHealth whether the fields of study for the dataset identified
     *                                           by this DIF include Human Health
     *
     * @return void
     */
    public function setFieldOfStudyHumanHealth(?bool $fieldOfStudyHumanHealth)
    {
        $this->fieldOfStudyHumanHealth = $fieldOfStudyHumanHealth;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @return bool RETURNDESCRIPTION
     */
    public function hasFieldOfStudyHumanHealth()
    {
        return $this->fieldOfStudyHumanHealth;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @param bool|null $fieldOfStudySocialCulturalPolitical whether the fields of study for the dataset identified
     *                                                       by this DIF include Social/Cultural/Political
     *
     * @return void
     */
    public function setFieldOfStudySocialCulturalPolitical(?bool $fieldOfStudySocialCulturalPolitical)
    {
        $this->fieldOfStudySocialCulturalPolitical = $fieldOfStudySocialCulturalPolitical;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @return bool whether the fields of study for the dataset identified
     *              by this DIF include Social/Cultural/Political
     */
    public function hasFieldOfStudySocialCulturalPolitical()
    {
        return $this->fieldOfStudySocialCulturalPolitical;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @param bool|null $fieldOfStudyEconomics whether the fields of study for the dataset identified
     *                                         by this DIF include Economics
     *
     * @return void
     */
    public function setFieldOfStudyEconomics(?bool $fieldOfStudyEconomics)
    {
        $this->fieldOfStudyEconomics = $fieldOfStudyEconomics;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @return bool whether the fields of study for the dataset identified
     *              by this DIF include Economics
     */
    public function hasFieldOfStudyEconomics()
    {
        return $this->fieldOfStudyEconomics;
    }

    /**
     * Sets other fields of study for the dataset identified by this DIF.
     *
     * @param string|null $fieldOfStudyOther other fields of study for the dataset identified by this DIF
     *
     * @return void
     */
    public function setFieldOfStudyOther(?string $fieldOfStudyOther)
    {
        $this->fieldOfStudyOther = $fieldOfStudyOther;
    }

    /**
     * Gets other fields of study for the dataset identified by this DIF.
     *
     * @return string other fields of study for the dataset identified by this DIF
     */
    public function getFieldOfStudyOther()
    {
        return $this->fieldOfStudyOther;
    }

    /**
     * Sets the approximate data size for the dataset identified by this DIF.
     *
     * @param string|null $dataSize the approximate data size for the dataset identified by this DIF
     *
     * @return void
     *
     * @throws \InvalidArgumentException when $dataSize is not a valid data size
     *
     * @see DATA_SIZES class constant for valid values.
     */
    public function setDataSize(?string $dataSize)
    {
        if (!in_array($dataSize, self::DATA_SIZES)) {
            throw new \InvalidArgumentException('Data size must be one of: ' . implode(', ', self::DATA_SIZES));
        }
        $this->dataSize = $dataSize;
    }

    /**
     * Gets the approximate data size for the dataset identified by this DIF.
     *
     * @return string the approximate data size for the dataset identified by this DIF
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * Sets the Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @param string|null $variablesObserved the Phenomenon/Variables Observed or Generated
     *                                       for the dataset identified by this DIF
     *
     * @return void
     */
    public function setVariablesObserved(?string $variablesObserved)
    {
        $this->variablesObserved = $variablesObserved;
    }

    /**
     * Gets the Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @return string the Phenomenon/Variables Observed or Generated
     *                for the dataset identified by this DIF
     */
    public function getVariablesObserved()
    {
        return $this->variablesObserved;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @param bool|null $collectionMethodFieldSampling whether the methods for collecting or generating the dataset
     *                                                 identified by this DIF include Field Sampling
     *
     * @return void
     */
    public function setCollectionMethodFieldSampling(?bool $collectionMethodFieldSampling)
    {
        $this->collectionMethodFieldSampling = $collectionMethodFieldSampling;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @return bool whether the methods for collecting or generating the dataset
     *              identified by this DIF include Field Sampling
     */
    public function hasCollectionMethodFieldSampling()
    {
        return $this->collectionMethodFieldSampling;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @param bool|null $collectionMethodSimulatedGenerated whether the methods for collecting or generating the dataset
     *                                                      identified by this DIF include Simulated/Generated
     *
     * @return void
     */
    public function setCollectionMethodSimulatedGenerated(?bool $collectionMethodSimulatedGenerated)
    {
        $this->collectionMethodSimulatedGenerated = $collectionMethodSimulatedGenerated;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @return bool whether the methods for collecting or generating the dataset
     *              identified by this DIF include Simulated/Generated
     */
    public function hasCollectionMethodSimulatedGenerated()
    {
        return $this->collectionMethodSimulatedGenerated;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @param bool|null $collectionMethodLaboratory whether the methods for collecting or generating the dataset
     *                                              identified by this DIF include Laboratory
     *
     * @return void
     */
    public function setCollectionMethodLaboratory(?bool $collectionMethodLaboratory)
    {
        $this->collectionMethodLaboratory = $collectionMethodLaboratory;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @return bool whether the methods for collecting or generating the dataset
     *              identified by this DIF include Laboratory
     */
    public function hasCollectionMethodLaboratory()
    {
        return $this->collectionMethodLaboratory;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @param bool|null $collectionMethodLiteratureBased whether the methods for collecting or generating the dataset
     *                                                   identified by this DIF include Literature Based
     *
     * @return void
     */
    public function setCollectionMethodLiteratureBased(?bool $collectionMethodLiteratureBased)
    {
        $this->collectionMethodLiteratureBased = $collectionMethodLiteratureBased;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @return bool whether the methods for collecting or generating the dataset
     *              dentified by this DIF include Literature Based
     */
    public function hasCollectionMethodLiteratureBased()
    {
        return $this->collectionMethodLiteratureBased;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @param bool $collectionMethodRemoteSensing whether the methods for collecting or generating the dataset
     *                                            identified by this DIF include Remote Sensing
     *
     * @return void
     */
    public function setCollectionMethodRemoteSensing(?bool $collectionMethodRemoteSensing)
    {
        $this->collectionMethodRemoteSensing = $collectionMethodRemoteSensing;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @return bool whether the methods for collecting or generating the dataset
     *              identified by this DIF include Remote Sensing
     */
    public function hasCollectionMethodRemoteSensing()
    {
        return $this->collectionMethodRemoteSensing;
    }

    /**
     * Sets other methods for collecting or generating the dataset identified by this DIF.
     *
     * @param string|null $collectionMethodOther other methods for collecting or generating
     *                                           the dataset identified by this DIF
     *
     * @return void
     */
    public function setCollectionMethodOther(?string $collectionMethodOther)
    {
        $this->collectionMethodOther = $collectionMethodOther;
    }

    /**
     * Other methods for collecting or generating the dataset identified by this DIF.
     *
     * @return string other methods for collecting or generating the dataset identified by this DIF
     */
    public function getCollectionMethodOther()
    {
        return $this->collectionMethodOther;
    }

    /**
     * Sets the start date of the estimated data sampling/generation period.
     *
     * @param \DateTime|null $estimatedStartDate the start date of the estimated data sampling/generation period
     *
     * @return void
     */
    public function setEstimatedStartDate(\DateTime $estimatedStartDate = null)
    {
        $this->estimatedStartDate = $estimatedStartDate;
    }

    /**
     * Gets the start date of the estimated data sampling/generation period.
     *
     * @return \DateTime the start date of the estimated data sampling/generation period
     */
    public function getEstimatedStartDate()
    {
        return $this->estimatedStartDate;
    }

    /**
     * Sets the end date of the estimated data sampling/generation period.
     *
     * @param \DateTime|null $estimatedEndDate the end date of the estimated data sampling/generation period
     *
     * @return void
     */
    public function setEstimatedEndDate(\DateTime $estimatedEndDate = null)
    {
        $this->estimatedEndDate = $estimatedEndDate;
    }

    /**
     * The end date of the estimated data sampling/generation period.
     *
     * @return \DateTime the end date of the estimated data sampling/generation period
     */
    public function getEstimatedEndDate()
    {
        return $this->estimatedEndDate;
    }

    /**
     * Sets a text description of the spatial extent.
     *
     * @param string|null $spatialExtentDescription a text description of the spatial extent
     *
     * @return void
     */
    public function setSpatialExtentDescription(?string $spatialExtentDescription)
    {
        $this->spatialExtentDescription = $spatialExtentDescription;
    }

    /**
     * Gets the text description of the spatial extent.
     *
     * @return string the text description of the spatial extent
     */
    public function getSpatialExtentDescription()
    {
        return $this->spatialExtentDescription;
    }

    /**
     * Sets the geometry for the spatial extent.
     *
     * @param string|null $spatialExtentGeometry the geometry for the spatial extent
     *
     * @return void
     */
    public function setSpatialExtentGeometry(?string $spatialExtentGeometry)
    {
        $this->spatialExtentGeometry = $spatialExtentGeometry;
    }

    /**
     * The geometry for the spatial extent.
     *
     * @return string the geometry for the spatial extent
     */
    public function getSpatialExtentGeometry()
    {
        return $this->spatialExtentGeometry;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @param bool|null $nationalDataArchiveNODC whether the national data archives for the dataset identified
     *                                           by this DIF include the National Oceanographic Data Center (NODC)
     *
     * @return void
     */
    public function setNationalDataArchiveNODC(?bool $nationalDataArchiveNODC)
    {
        $this->nationalDataArchiveNODC = $nationalDataArchiveNODC;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @return bool whether the national data archives for the dataset identified
     *              by this DIF include the National Oceanographic Data Center (NODC)
     */
    public function hasNationalDataArchiveNODC()
    {
        return $this->nationalDataArchiveNODC;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @param bool|null $nationalDataArchiveStoret whether the national data archives for the dataset identified
     *                                             by this DIF include US EPA Storet
     *
     * @return void
     */
    public function setNationalDataArchiveStoret(?bool $nationalDataArchiveStoret)
    {
        $this->nationalDataArchiveStoret = $nationalDataArchiveStoret;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @return bool whether the national data archives for the dataset identified
     *              by this DIF include US EPA Storet
     */
    public function hasNationalDataArchiveStoret()
    {
        return $this->nationalDataArchiveStoret;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @param bool|null $nationalDataArchiveGBIF whether the national data archives for the dataset identified
     *                                           by this DIF include the Global Biodiversity Information Facility
     *
     * @return void
     */
    public function setNationalDataArchiveGBIF(?bool $nationalDataArchiveGBIF)
    {
        $this->nationalDataArchiveGBIF = $nationalDataArchiveGBIF;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @return bool whether the national data archives for the dataset identified
     *              by this DIF include the Global Biodiversity Information Facility
     */
    public function hasNationalDataArchiveGBIF()
    {
        return $this->nationalDataArchiveGBIF;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @param bool|null $nationalDataArchiveNCBI whether the national data archives for the dataset identified
     *                                           by this DIF include the National Center for Biotechnology Information
     *
     * @return void
     */
    public function setNationalDataArchiveNCBI(?bool $nationalDataArchiveNCBI)
    {
        $this->nationalDataArchiveNCBI = $nationalDataArchiveNCBI;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @return bool whether the national data archives for the dataset identified
     *              by this DIF include the National Center for Biotechnology Information
     */
    public function hasNationalDataArchiveNCBI()
    {
        return $this->nationalDataArchiveNCBI;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @param bool|null $nationalDataArchiveDataGov Whether the national data archives for the dataset identified
     *                                              by this DIF include the Data.gov Dataset Management System.
     *
     * @return void
     */
    public function setNationalDataArchiveDataGov(?bool $nationalDataArchiveDataGov)
    {
        $this->nationalDataArchiveDataGov = $nationalDataArchiveDataGov;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @return bool Whether the national data archives for the dataset identified
     *              by this DIF include the Data.gov Dataset Management System.
     */
    public function hasNationalDataArchiveDataGov()
    {
        return $this->nationalDataArchiveDataGov;
    }

    /**
     * Sets Other national data archives for the dataset identified by this DIF.
     *
     * @param string|null $nationalDataArchiveOther other national data archives for the dataset identified by this DIF
     *
     * @return void
     */
    public function setNationalDataArchiveOther(?string $nationalDataArchiveOther)
    {
        $this->nationalDataArchiveOther = $nationalDataArchiveOther;
    }

    /**
     * Other national data archives for the dataset identified by this DIF.
     *
     * @return string other national data archives for the dataset identified by this DIF
     */
    public function getNationalDataArchiveOther()
    {
        return $this->nationalDataArchiveOther;
    }

    /**
     * Sets whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @param string|null $ethicalIssues whether the dataset identified by this DIF will include any data
     *                                   governed under the IRB or HIPAA, or data with other ethical issues
     *
     * @return void
     *
     * @throws \InvalidArgumentException when $ethicalIssues is not a valid value
     */
    public function setEthicalIssues(?string $ethicalIssues)
    {
        if (!in_array($ethicalIssues, self::ETHICAL_ISSUES)) {
            throw new \InvalidArgumentException('Ethical issues must be one of: ' . implode(', ', self::ETHICAL_ISSUES));
        }

        $this->ethicalIssues = $ethicalIssues;
    }

    /**
     * Gets whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @return string whether the dataset identified by this DIF will include any data
     *                governed under the IRB or HIPAA, or data with other ethical issues
     */
    public function getEthicalIssues()
    {
        return $this->ethicalIssues;
    }

    /**
     * Sets an explanation of ethical issues for the dataset identified by this DIF.
     *
     * @param string|null $ethicalIssuesExplanation an explanation of ethical issues for the dataset identified by this DIF
     *
     * @return void
     */
    public function setEthicalIssuesExplanation(?string $ethicalIssuesExplanation)
    {
        $this->ethicalIssuesExplanation = $ethicalIssuesExplanation;
    }

    /**
     * An explanation of ethical issues for the dataset identified by this DIF.
     *
     * @return string an explanation of ethical issues for the dataset identified by this DIF
     */
    public function getEthicalIssuesExplanation()
    {
        return $this->ethicalIssuesExplanation;
    }

    /**
     * Sets additional remarks for this DIF.
     *
     * @param string|null $remarks additional remarks for this DIF
     *
     * @return void
     */
    public function setRemarks(?string $remarks)
    {
        $this->remarks = $remarks;
    }

    /**
     * Gets additional remarks for this DIF.
     *
     * @return string additional remarks for this DIF
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Whether or not this DIF can be submitted.
     *
     * @return bool true if this DIF can be submitted, False otherwise
     */
    public function isSubmittable(): bool
    {
        return self::STATUS_UNSUBMITTED === $this->status;
    }

    /**
     * Submit this DIF.
     *
     * This will set the DIF's status to submitted when it's current status is unsubmitted,
     *
     * @return void
     *
     * @throws \Exception when a DIF's status is anything other than unsubmitted
     */
    public function submit()
    {
        if ($this->isSubmittable()) {
            $this->setStatus(self::STATUS_SUBMITTED);
        } else {
            throw new \Exception('Can only submit an unsubmitted DIF');
        }
    }

    /**
     * Whether or not this DIF can be approved.
     *
     * @return bool true if this DIF can be approved, False otherwise
     */
    public function isApprovable()
    {
        return self::STATUS_SUBMITTED === $this->status;
    }

    /**
     * Approve this DIF.
     *
     * This will set the DIF's status to approved when its current status is submitted,
     *
     * @return void
     *
     * @throws \Exception when a DIF's status is anything other than submitted
     */
    public function approve()
    {
        if ($this->isApprovable()) {
            $this->setStatus(self::STATUS_APPROVED);
            $this->setApprovedDate(new \DateTime('now', new \DateTimeZone('UTC')));
        } else {
            throw new \Exception('Can only approve a submitted DIF');
        }
    }

    /**
     * Whether or not this DIF can be rejected.
     *
     * @return bool true if this DIF can be rejected, False otherwise
     */
    public function isRejectable()
    {
        return self::STATUS_SUBMITTED === $this->status;
    }

    /**
     * Reject this DIF.
     *
     * This will set the DIF's status to unsubmitted when its current status is submitted,
     *
     * @return void
     *
     * @throws \Exception when a DIF's status is anything other than unsubmitted
     */
    public function reject()
    {
        if ($this->isRejectable()) {
            $this->setStatus(self::STATUS_UNSUBMITTED);
        } else {
            throw new \Exception('Can only reject a submitted DIF');
        }
    }

    /**
     * Whether or not this DIF can be unlocked.
     *
     * @return bool true if this DIF can be unlocked, False otherwise
     */
    public function isUnlockable()
    {
        return self::STATUS_APPROVED === $this->status;
    }

    /**
     * Unlock this DIF.
     *
     * This will set the DIF's status to unsubmitted when its current status is submitted or approved,
     *
     * @return void
     *
     * @throws \Exception when a DIF's status is anything other than submitted or approved
     */
    public function unlock()
    {
        if ($this->isUnlockable()) {
            $this->setStatus(self::STATUS_UNSUBMITTED);
        } else {
            throw new \Exception('Can only unlock an approved DIF');
        }
    }

    /**
     * Whether a DIF is locked or not.
     *
     * @return bool true if a DIF is locked, False otherwise
     */
    public function isLocked()
    {
        // A DIF is locked if its status is anything other than unsubmitted.
        return self::STATUS_UNSUBMITTED !== $this->status;
    }

    /**
     * Set the DIF status.
     *
     * @param int $status the status of this DIF
     *
     * @return void
     */
    protected function setStatus(int $status)
    {
        $this->status = $status;
        $this->updateIdentifiedStatus();
    }

    /**
     * Update the identified status in associated Dataset if a Dataset has been associated.
     *
     * @return void
     */
    protected function updateIdentifiedStatus()
    {
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->setIdentifiedStatus($this->status);
        }
    }

    /**
     * Getter to approved date.
     *
     * @return \DateTime
     */
    public function getApprovedDate()
    {
        return $this->approvedDate;
    }

    /**
     * Setter to approved date.
     *
     * @param \DateTime $approvedDate the approved date for the DIF
     *
     * @return void
     */
    public function setApprovedDate(\DateTime $approvedDate)
    {
        $this->approvedDate = $approvedDate;
    }

    /**
     * Sets the issue tracking ticket for this Dataset.
     *
     * @param string|null $issueTrackingTicket the identifier for an issue tracking ticket related to this Dataset
     */
    public function setIssueTrackingTicket(?string $issueTrackingTicket): self
    {
        $dataset = $this->getDataset();
        if ($dataset instanceof Dataset) {
            $dataset->setIssueTrackingTicket($issueTrackingTicket);
        }

        return $this;
    }

    /**
     * Gets the issue tracking ticket for this Dataset.
     *
     * @Serializer\VirtualProperty
     *
     * @Serializer\SerializedName("issueTrackingTicket")
     */
    public function getIssueTrackingTicket(): string
    {
        $dataset = $this->getDataset();
        if ($dataset instanceof Dataset) {
            return $dataset->getIssueTrackingTicket() ?? '';
        }

        return '';
    }

    /**
     * Get the funders for this Dataset.
     */
    public function getFunders(): ?Collection
    {
        return $this->getDataset()?->getFunders() ?? null;
    }

    /**
     * Set the additional funders.
     */
    public function setAdditionalFunders(?string $additionalFunders): self
    {
        $this->additionalFunders = $additionalFunders;

        return $this;
    }

    /**
     * Get the additional funders.
     */
    public function getAdditionalFunders(): ?string
    {
        return $this->additionalFunders;
    }
}
