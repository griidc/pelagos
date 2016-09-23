<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

use JMS\Serializer\Annotation as Serializer;

/**
 * DIF Entity class.
 *
 * @ORM\Entity
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorizationchecker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "submit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_submit",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isSubmittable() or not service('security.authorizationchecker').isGranted(['CAN_SUBMIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "approve",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_approve",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isApprovable() or not service('security.authorizationchecker').isGranted(['CAN_APPROVE'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "reject",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_reject",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isRejectable() or not service('security.authorizationchecker').isGranted(['CAN_REJECT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "unlock",
 *   href = @Hateoas\Route(
 *     "pelagos_api_difs_unlock",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isUnlockable() or not service('security.authorizationchecker').isGranted(['CAN_UNLOCK'], object))"
 *   )
 * )
 */
class DIF extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'DIF';

    /**
     * Status value for a DIF that has been saved, but not submitted.
     */
    const STATUS_UNSUBMITTED = 0;

    /**
     * Status value for a DIF that has been submitted, but not yet approved.
     */
    const STATUS_SUBMITTED = 1;

    /**
     * Status value for a DIF that has been approved.
     */
    const STATUS_APPROVED = 2;

    /**
     * Valid data sizes for $dataSize.
     */
    const DATA_SIZES = array('< 1GB', '1GB-10GB', '10GB-200GB', '200GB-1TB', '1TB-5TB', '>5TB');

    /**
     * Valid values for $ethicalIssues.
     */
    const ETHICAL_ISSUES = array('No', 'Yes', 'Uncertain');

    /**
     * The Dataset this DIF identifies.
     *
     * @var Dataset
     *
     * @ORM\OneToOne(targetEntity="Dataset", mappedBy="dif", cascade={"persist"})
     */
    protected $dataset;

    /**
     * The status of this DIF.
     *
     * @var integer
     *
     * @see STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $status = self::STATUS_UNSUBMITTED;

    /**
     * The title for this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="Title is required"
     * )
     */
    protected $title;

    /**
     * The primary point of contact for this DIF.
     *
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     *
     * @Assert\NotBlank(
     *     message="Primary Point of Contact is required"
     * )
     */
    protected $primaryPointOfContact;

    /**
     * The secondary point of contact for this DIF.
     *
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    protected $secondaryPointOfContact;

    /**
     * The abstract for this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="Abstract is required"
     * )
     */
    protected $abstract;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyEcologicalBiological;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyPhysicalOceanography;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyAtmospheric;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyChemical;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyHumanHealth;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudySocialCulturalPolitical;

    /**
     * Whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fieldOfStudyEconomics;

    /**
     * Other fields of study for the dataset identified by this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fieldOfStudyOther;

    /**
     * The approximate data size for the dataset identified by this DIF.
     *
     * @var string
     *
     * @see DATA_SIZES class constant for valid values.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $dataSize;

    /**
     * The Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $variablesObserved;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $collectionMethodFieldSampling;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $collectionMethodSimulatedGenerated;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $collectionMethodLaboratory;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $collectionMethodLiteratureBased;

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $collectionMethodRemoteSensing;

    /**
     * Other methods for collecting or generating the dataset identified by this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $collectionMethodOther;

    /**
     * The start date of the estimated data sampling/generation period.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Serializer\Type("DateTime<'Y-m-d'>")
     */
    protected $estimatedStartDate;

    /**
     * The end date of the estimated data sampling/generation period.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Serializer\Type("DateTime<'Y-m-d'>")
     */
    protected $estimatedEndDate;

    /**
     * A text description of the spatial extent.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $spatialExtentDescription;

    /**
     * The geometry for the spatial extent.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $spatialExtentGeometry;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalDataArchiveNODC;

    /**
     * Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalDataArchiveStoret;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalDataArchiveGBIF;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalDataArchiveNCBI;

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $nationalDataArchiveDataGov;

    /**
     * Other national data archives for the dataset identified by this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nationalDataArchiveOther;

    /**
     * Whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @var string
     *
     * @see ETHICAL_ISSUES class constant for valid values;
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ethicalIssues;

    /**
     * An explanation of ethical issues for the dataset identified by this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ethicalIssuesExplanation;

    /**
     * Additional remarks for this DIF.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $remarks;

    /**
     * Constructor.
     *
     * Initializes status to unsubmitted.
     *
     * @param Dataset $dataset The dataset this DIF identifies.
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
     * @param Dataset|null $dataset The Dataset this DIF identifies.
     *
     * @return void
     */
    public function setDataset(Dataset $dataset = null)
    {
        $this->dataset = $dataset;
        if ($dataset !== null and $this->dataset->getDif() !== $this) {
            $this->dataset->setDif($this);
        }
        $this->updateIdentifiedStatus();
    }

    /**
     * Gets the Dataset this DIF identifies.
     *
     * @return Dataset The Dataset this DIF identifies.
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
     * @return integer The status of this DIF.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the Research Group this DIF is attached to.
     *
     * @param ResearchGroup|null $researchGroup The Research Group this DIF is attached to.
     *
     * @throws \Exception When the DIF does not yet have a Dataset.
     *
     * @return void
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
     * @return ResearchGroup The Research Group this DIF is attached to.
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
     * @param string $title The title for this DIF.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Gets the title for this DIF.
     *
     * @return string The title for this DIF.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the primary point of contact for this DIF.
     *
     * @param Person|null $primaryPointOfContact The primary point of contact for this DIF.
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
     * @return Person The primary point of contact for this DIF.
     */
    public function getPrimaryPointOfContact()
    {
        return $this->primaryPointOfContact;
    }

    /**
     * Sets the secondary point of contact for this DIF.
     *
     * @param Person|null $secondaryPointOfContact The secondary point of contact for this DIF.
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
     * @return Person The secondary point of contact for this DIF.
     */
    public function getSecondaryPointOfContact()
    {
        return $this->secondaryPointOfContact;
    }

    /**
     * Sets the abstract for this DIF.
     *
     * @param string $abstract The abstract for this DIF.
     *
     * @return void
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Gets the abstract for this DIF.
     *
     * @return string The abstract for this DIF.
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @param boolean $fieldOfStudyEcologicalBiological Whether the fields of study for the dataset identified
     *                                                  by this DIF include Ecological/Biological.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyEcologicalBiological is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyEcologicalBiological($fieldOfStudyEcologicalBiological)
    {
        if ('boolean' !== gettype($fieldOfStudyEcologicalBiological)) {
            throw new \InvalidArgumentException('fieldOfStudyEcologicalBiological must be boolean');
        }
        $this->fieldOfStudyEcologicalBiological = $fieldOfStudyEcologicalBiological;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Ecological/Biological.
     *
     * @return boolean Whether the fields of study for the dataset identified
     *                 by this DIF include Ecological/Biological.
     */
    public function hasFieldOfStudyEcologicalBiological()
    {
        return $this->fieldOfStudyEcologicalBiological;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @param boolean $fieldOfStudyPhysicalOceanography Whether the fields of study for the dataset identified
     *                                                  by this DIF include Physical Oceanography.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyPhysicalOceanography is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyPhysicalOceanography($fieldOfStudyPhysicalOceanography)
    {
        if ('boolean' !== gettype($fieldOfStudyPhysicalOceanography)) {
            throw new \InvalidArgumentException('fieldOfStudyPhysicalOceanography must be boolean');
        }
        $this->fieldOfStudyPhysicalOceanography = $fieldOfStudyPhysicalOceanography;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Physical Oceanography.
     *
     * @return boolean Whether the fields of study for the dataset identified
     *                 by this DIF include Physical Oceanography.
     */
    public function hasFieldOfStudyPhysicalOceanography()
    {
        return $this->fieldOfStudyPhysicalOceanography;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @param boolean $fieldOfStudyAtmospheric Whether the fields of study for the dataset identified
     *                                         by this DIF include Atmospheric.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyAtmospheric is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyAtmospheric($fieldOfStudyAtmospheric)
    {
        if ('boolean' !== gettype($fieldOfStudyAtmospheric)) {
            throw new \InvalidArgumentException('fieldOfStudyAtmospheric must be boolean');
        }
        $this->fieldOfStudyAtmospheric = $fieldOfStudyAtmospheric;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Atmospheric.
     *
     * @return boolean Whether the fields of study for the dataset identified by this DIF include Atmospheric.
     */
    public function hasFieldOfStudyAtmospheric()
    {
        return $this->fieldOfStudyAtmospheric;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @param boolean $fieldOfStudyChemical Whether the fields of study for the dataset identified
     *                                      by this DIF include Chemical.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyChemical is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyChemical($fieldOfStudyChemical)
    {
        if ('boolean' !== gettype($fieldOfStudyChemical)) {
            throw new \InvalidArgumentException('fieldOfStudyChemical must be boolean');
        }
        $this->fieldOfStudyChemical = $fieldOfStudyChemical;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Chemical.
     *
     * @return boolean Whether the fields of study for the dataset identified by this DIF include Chemical.
     */
    public function hasFieldOfStudyChemical()
    {
        return $this->fieldOfStudyChemical;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @param boolean $fieldOfStudyHumanHealth Whether the fields of study for the dataset identified
     *                                         by this DIF include Human Health.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyHumanHealth is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyHumanHealth($fieldOfStudyHumanHealth)
    {
        if ('boolean' !== gettype($fieldOfStudyHumanHealth)) {
            throw new \InvalidArgumentException('fieldOfStudyHumanHealth must be boolean');
        }
        $this->fieldOfStudyHumanHealth = $fieldOfStudyHumanHealth;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Human Health.
     *
     * @return boolean RETURNDESCRIPTION
     */
    public function hasFieldOfStudyHumanHealth()
    {
        return $this->fieldOfStudyHumanHealth;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @param boolean $fieldOfStudySocialCulturalPolitical Whether the fields of study for the dataset identified
     *                                                     by this DIF include Social/Cultural/Political.
     *
     * @throws \InvalidArgumentException When $fieldOfStudySocialCulturalPolitical is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudySocialCulturalPolitical($fieldOfStudySocialCulturalPolitical)
    {
        if ('boolean' !== gettype($fieldOfStudySocialCulturalPolitical)) {
            throw new \InvalidArgumentException('fieldOfStudySocialCulturalPolitical must be boolean');
        }
        $this->fieldOfStudySocialCulturalPolitical = $fieldOfStudySocialCulturalPolitical;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Social/Cultural/Political.
     *
     * @return boolean Whether the fields of study for the dataset identified
     *                 by this DIF include Social/Cultural/Political.
     */
    public function hasFieldOfStudySocialCulturalPolitical()
    {
        return $this->fieldOfStudySocialCulturalPolitical;
    }

    /**
     * Sets whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @param boolean $fieldOfStudyEconomics Whether the fields of study for the dataset identified
     *                                       by this DIF include Economics.
     *
     * @throws \InvalidArgumentException When $fieldOfStudyEconomics is not boolean.
     *
     * @return void
     */
    public function setFieldOfStudyEconomics($fieldOfStudyEconomics)
    {
        if ('boolean' !== gettype($fieldOfStudyEconomics)) {
            throw new \InvalidArgumentException('fieldOfStudyEconomics must be boolean');
        }
        $this->fieldOfStudyEconomics = $fieldOfStudyEconomics;
    }

    /**
     * Whether the fields of study for the dataset identified by this DIF include Economics.
     *
     * @return boolean Whether the fields of study for the dataset identified
     *                 by this DIF include Economics.
     */
    public function hasFieldOfStudyEconomics()
    {
        return $this->fieldOfStudyEconomics;
    }

    /**
     * Sets other fields of study for the dataset identified by this DIF.
     *
     * @param string $fieldOfStudyOther Other fields of study for the dataset identified by this DIF.
     *
     * @return void
     */
    public function setFieldOfStudyOther($fieldOfStudyOther)
    {
        $this->fieldOfStudyOther = $fieldOfStudyOther;
    }

    /**
     * Gets other fields of study for the dataset identified by this DIF.
     *
     * @return string Other fields of study for the dataset identified by this DIF.
     */
    public function getFieldOfStudyOther()
    {
        return $this->fieldOfStudyOther;
    }

    /**
     * Sets the approximate data size for the dataset identified by this DIF.
     *
     * @param string $dataSize The approximate data size for the dataset identified by this DIF.
     *
     * @throws \InvalidArgumentException When $dataSize is not a valid data size.
     *
     * @see DATA_SIZES class constant for valid values.
     *
     * @return void
     */
    public function setDataSize($dataSize)
    {
        if (!in_array($dataSize, self::DATA_SIZES)) {
            throw new \InvalidArgumentException('Data size must be one of: ' . implode(', ', self::DATA_SIZES));
        }
        $this->dataSize = $dataSize;
    }

    /**
     * Gets the approximate data size for the dataset identified by this DIF.
     *
     * @return string The approximate data size for the dataset identified by this DIF.
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * Sets the Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @param string $variablesObserved The Phenomenon/Variables Observed or Generated
     *                                  for the dataset identified by this DIF.
     *
     * @return void
     */
    public function setVariablesObserved($variablesObserved)
    {
        $this->variablesObserved = $variablesObserved;
    }

    /**
     * Gets the Phenomenon/Variables Observed or Generated for the dataset identified by this DIF.
     *
     * @return string The Phenomenon/Variables Observed or Generated
     *                for the dataset identified by this DIF.
     */
    public function getVariablesObserved()
    {
        return $this->variablesObserved;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @param boolean $collectionMethodFieldSampling Whether the methods for collecting or generating the dataset
     *                                               identified by this DIF include Field Sampling.
     *
     * @throws \InvalidArgumentException When $collectionMethodFieldSampling is not boolean.
     *
     * @return void
     */
    public function setCollectionMethodFieldSampling($collectionMethodFieldSampling)
    {
        if ('boolean' !== gettype($collectionMethodFieldSampling)) {
            throw new \InvalidArgumentException('collectionMethodFieldSampling must be boolean');
        }
        $this->collectionMethodFieldSampling = $collectionMethodFieldSampling;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Field Sampling.
     *
     * @return boolean Whether the methods for collecting or generating the dataset
     *                 identified by this DIF include Field Sampling.
     */
    public function hasCollectionMethodFieldSampling()
    {
        return $this->collectionMethodFieldSampling;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @param boolean $collectionMethodSimulatedGenerated Whether the methods for collecting or generating the dataset
     *                                                    identified by this DIF include Simulated/Generated.
     *
     * @throws \InvalidArgumentException When $collectionMethodSimulatedGenerated is not boolean.
     *
     * @return void
     */
    public function setCollectionMethodSimulatedGenerated($collectionMethodSimulatedGenerated)
    {
        if ('boolean' !== gettype($collectionMethodSimulatedGenerated)) {
            throw new \InvalidArgumentException('collectionMethodSimulatedGenerated must be boolean');
        }
        $this->collectionMethodSimulatedGenerated = $collectionMethodSimulatedGenerated;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Simulated/Generated.
     *
     * @return boolean Whether the methods for collecting or generating the dataset
     *                 identified by this DIF include Simulated/Generated.
     */
    public function hasCollectionMethodSimulatedGenerated()
    {
        return $this->collectionMethodSimulatedGenerated;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @param boolean $collectionMethodLaboratory Whether the methods for collecting or generating the dataset
     *                                            identified by this DIF include Laboratory.
     *
     * @throws \InvalidArgumentException When $collectionMethodLaboratory is not boolean.
     *
     * @return void
     */
    public function setCollectionMethodLaboratory($collectionMethodLaboratory)
    {
        if ('boolean' !== gettype($collectionMethodLaboratory)) {
            throw new \InvalidArgumentException('collectionMethodLaboratory must be boolean');
        }
        $this->collectionMethodLaboratory = $collectionMethodLaboratory;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Laboratory.
     *
     * @return boolean Whether the methods for collecting or generating the dataset
     *                 identified by this DIF include Laboratory.
     */
    public function hasCollectionMethodLaboratory()
    {
        return $this->collectionMethodLaboratory;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @param boolean $collectionMethodLiteratureBased Whether the methods for collecting or generating the dataset
     *                                                 identified by this DIF include Literature Based.
     *
     * @throws \InvalidArgumentException When $collectionMethodLiteratureBased is not boolean.
     *
     * @return void
     */
    public function setCollectionMethodLiteratureBased($collectionMethodLiteratureBased)
    {
        if ('boolean' !== gettype($collectionMethodLiteratureBased)) {
            throw new \InvalidArgumentException('collectionMethodLiteratureBased must be boolean');
        }
        $this->collectionMethodLiteratureBased = $collectionMethodLiteratureBased;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Literature Based.
     *
     * @return boolean Whether the methods for collecting or generating the dataset
     *                 dentified by this DIF include Literature Based.
     */
    public function hasCollectionMethodLiteratureBased()
    {
        return $this->collectionMethodLiteratureBased;
    }

    /**
     * Sets whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @param boolean $collectionMethodRemoteSensing Whether the methods for collecting or generating the dataset
     *                                               identified by this DIF include Remote Sensing.
     *
     * @throws \InvalidArgumentException When $collectionMethodRemoteSensing is not boolean.
     *
     * @return void
     */
    public function setCollectionMethodRemoteSensing($collectionMethodRemoteSensing)
    {
        if ('boolean' !== gettype($collectionMethodRemoteSensing)) {
            throw new \InvalidArgumentException('collectionMethodRemoteSensing must be boolean');
        }
        $this->collectionMethodRemoteSensing = $collectionMethodRemoteSensing;
    }

    /**
     * Whether the methods for collecting or generating the dataset identified by this DIF include Remote Sensing.
     *
     * @return boolean Whether the methods for collecting or generating the dataset
     *                 identified by this DIF include Remote Sensing.
     */
    public function hasCollectionMethodRemoteSensing()
    {
        return $this->collectionMethodRemoteSensing;
    }

    /**
     * Sets other methods for collecting or generating the dataset identified by this DIF.
     *
     * @param string $collectionMethodOther Other methods for collecting or generating
     *                                      the dataset identified by this DIF.
     *
     * @return void
     */
    public function setCollectionMethodOther($collectionMethodOther)
    {
        $this->collectionMethodOther = $collectionMethodOther;
    }

    /**
     * Other methods for collecting or generating the dataset identified by this DIF.
     *
     * @return string Other methods for collecting or generating the dataset identified by this DIF.
     */
    public function getCollectionMethodOther()
    {
        return $this->collectionMethodOther;
    }

    /**
     * Sets the start date of the estimated data sampling/generation period.
     *
     * @param \DateTime|null $estimatedStartDate The start date of the estimated data sampling/generation period.
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
     * @return \DateTime The start date of the estimated data sampling/generation period.
     */
    public function getEstimatedStartDate()
    {
        return $this->estimatedStartDate;
    }

    /**
     * Sets the end date of the estimated data sampling/generation period.
     *
     * @param \DateTime|null $estimatedEndDate The end date of the estimated data sampling/generation period.
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
     * @return \DateTime The end date of the estimated data sampling/generation period.
     */
    public function getEstimatedEndDate()
    {
        return $this->estimatedEndDate;
    }

    /**
     * Sets a text description of the spatial extent.
     *
     * @param string $spatialExtentDescription A text description of the spatial extent.
     *
     * @return void
     */
    public function setSpatialExtentDescription($spatialExtentDescription)
    {
        $this->spatialExtentDescription = $spatialExtentDescription;
    }

    /**
     * Gets the text description of the spatial extent.
     *
     * @return string The text description of the spatial extent.
     */
    public function getSpatialExtentDescription()
    {
        return $this->spatialExtentDescription;
    }

    /**
     * Sets the geometry for the spatial extent.
     *
     * @param string $spatialExtentGeometry The geometry for the spatial extent.
     *
     * @return void
     */
    public function setSpatialExtentGeometry($spatialExtentGeometry)
    {
        $this->spatialExtentGeometry = $spatialExtentGeometry;
    }

    /**
     * The geometry for the spatial extent.
     *
     * @return string The geometry for the spatial extent.
     */
    public function getSpatialExtentGeometry()
    {
        return $this->spatialExtentGeometry;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @param boolean $nationalDataArchiveNODC Whether the national data archives for the dataset identified
     *                                         by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @throws \InvalidArgumentException When $nationalDataArchiveNODC is not boolean.
     *
     * @return void
     */
    public function setNationalDataArchiveNODC($nationalDataArchiveNODC)
    {
        if ('boolean' !== gettype($nationalDataArchiveNODC)) {
            throw new \InvalidArgumentException('nationalDataArchiveNODC must be boolean');
        }
        $this->nationalDataArchiveNODC = $nationalDataArchiveNODC;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Oceanographic Data Center (NODC).
     *
     * @return boolean Whether the national data archives for the dataset identified
     *                 by this DIF include the National Oceanographic Data Center (NODC).
     */
    public function hasNationalDataArchiveNODC()
    {
        return $this->nationalDataArchiveNODC;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @param boolean $nationalDataArchiveStoret Whether the national data archives for the dataset identified
     *                                           by this DIF include US EPA Storet.
     *
     * @throws \InvalidArgumentException When $nationalDataArchiveStoret is not boolean.
     *
     * @return void
     */
    public function setNationalDataArchiveStoret($nationalDataArchiveStoret)
    {
        if ('boolean' !== gettype($nationalDataArchiveStoret)) {
            throw new \InvalidArgumentException('nationalDataArchiveStoret must be boolean');
        }
        $this->nationalDataArchiveStoret = $nationalDataArchiveStoret;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include US EPA Storet.
     *
     * @return boolean Whether the national data archives for the dataset identified
     *                 by this DIF include US EPA Storet.
     */
    public function hasNationalDataArchiveStoret()
    {
        return $this->nationalDataArchiveStoret;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @param boolean $nationalDataArchiveGBIF Whether the national data archives for the dataset identified
     *                                         by this DIF include the Global Biodiversity Information Facility.
     *
     * @throws \InvalidArgumentException When $nationalDataArchiveGBIF is not boolean.
     *
     * @return void
     */
    public function setNationalDataArchiveGBIF($nationalDataArchiveGBIF)
    {
        if ('boolean' !== gettype($nationalDataArchiveGBIF)) {
            throw new \InvalidArgumentException('nationalDataArchiveGBIF must be boolean');
        }
        $this->nationalDataArchiveGBIF = $nationalDataArchiveGBIF;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Global Biodiversity Information Facility.
     *
     * @return boolean Whether the national data archives for the dataset identified
     *                 by this DIF include the Global Biodiversity Information Facility.
     */
    public function hasNationalDataArchiveGBIF()
    {
        return $this->nationalDataArchiveGBIF;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @param boolean $nationalDataArchiveNCBI Whether the national data archives for the dataset identified
     *                                         by this DIF include the National Center for Biotechnology Information.
     *
     * @throws \InvalidArgumentException When $nationalDataArchiveNCBI is not boolean.
     *
     * @return void
     */
    public function setNationalDataArchiveNCBI($nationalDataArchiveNCBI)
    {
        if ('boolean' !== gettype($nationalDataArchiveNCBI)) {
            throw new \InvalidArgumentException('nationalDataArchiveNCBI must be boolean');
        }
        $this->nationalDataArchiveNCBI = $nationalDataArchiveNCBI;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the National Center for Biotechnology Information.
     *
     * @return boolean Whether the national data archives for the dataset identified
     *                 by this DIF include the National Center for Biotechnology Information.
     */
    public function hasNationalDataArchiveNCBI()
    {
        return $this->nationalDataArchiveNCBI;
    }

    /**
     * Sets Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @param boolean $nationalDataArchiveDataGov Whether the national data archives for the dataset identified
     *                                            by this DIF include the Data.gov Dataset Management System.
     *
     * @throws \InvalidArgumentException When $nationalDataArchiveDataGov is not boolean.
     *
     * @return void
     */
    public function setNationalDataArchiveDataGov($nationalDataArchiveDataGov)
    {
        if ('boolean' !== gettype($nationalDataArchiveDataGov)) {
            throw new \InvalidArgumentException('nationalDataArchiveDataGov must be boolean');
        }
        $this->nationalDataArchiveDataGov = $nationalDataArchiveDataGov;
    }

    /**
     * Whether the national data archives for the dataset identified by this DIF include the Data.gov Dataset Management System.
     *
     * @return boolean Whether the national data archives for the dataset identified
     *                 by this DIF include the Data.gov Dataset Management System.
     */
    public function hasNationalDataArchiveDataGov()
    {
        return $this->nationalDataArchiveDataGov;
    }

    /**
     * Sets Other national data archives for the dataset identified by this DIF.
     *
     * @param string $nationalDataArchiveOther Other national data archives for the dataset identified by this DIF.
     *
     * @return void
     */
    public function setNationalDataArchiveOther($nationalDataArchiveOther)
    {
        $this->nationalDataArchiveOther = $nationalDataArchiveOther;
    }

    /**
     * Other national data archives for the dataset identified by this DIF.
     *
     * @return string Other national data archives for the dataset identified by this DIF.
     */
    public function getNationalDataArchiveOther()
    {
        return $this->nationalDataArchiveOther;
    }

    /**
     * Sets whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @param string $ethicalIssues Whether the dataset identified by this DIF will include any data
     *                              governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @throws \InvalidArgumentException When $ethicalIssues is not a valid value.
     *
     * @return void
     */
    public function setEthicalIssues($ethicalIssues)
    {
        if (!in_array($ethicalIssues, self::ETHICAL_ISSUES)) {
            throw new \InvalidArgumentException(
                'Ethical issues must be one of: ' . implode(', ', self::ETHICAL_ISSUES)
            );
        }
        $this->ethicalIssues = $ethicalIssues;
    }

    /**
     * Gets whether the dataset identified by this DIF will include any data governed under the IRB or HIPAA, or data with other ethical issues.
     *
     * @return string Whether the dataset identified by this DIF will include any data
     *                governed under the IRB or HIPAA, or data with other ethical issues.
     */
    public function getEthicalIssues()
    {
        return $this->ethicalIssues;
    }

    /**
     * Sets an explanation of ethical issues for the dataset identified by this DIF.
     *
     * @param string $ethicalIssuesExplanation An explanation of ethical issues for the dataset identified by this DIF.
     *
     * @return void
     */
    public function setEthicalIssuesExplanation($ethicalIssuesExplanation)
    {
        $this->ethicalIssuesExplanation = $ethicalIssuesExplanation;
    }

    /**
     * An explanation of ethical issues for the dataset identified by this DIF.
     *
     * @return string An explanation of ethical issues for the dataset identified by this DIF.
     */
    public function getEthicalIssuesExplanation()
    {
        return $this->ethicalIssuesExplanation;
    }

    /**
     * Sets additional remarks for this DIF.
     *
     * @param string $remarks Additional remarks for this DIF.
     *
     * @return void
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    }

    /**
     * Gets additional remarks for this DIF.
     *
     * @return string Additional remarks for this DIF.
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Whether or not this DIF can be submitted.
     *
     * @return boolean True if this DIF can be submitted, False otherwise.
     */
    public function isSubmittable()
    {
        return self::STATUS_UNSUBMITTED === $this->status;
    }

    /**
     * Submit this DIF.
     *
     * This will set the DIF's status to submitted when it's current status is unsubmitted,
     *
     * @throws \Exception When a DIF's status is anything other than unsubmitted.
     *
     * @return void
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
     * @return boolean True if this DIF can be approved, False otherwise.
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
     * @throws \Exception When a DIF's status is anything other than submitted.
     *
     * @return void
     */
    public function approve()
    {
        if ($this->isApprovable()) {
            $this->setStatus(self::STATUS_APPROVED);
        } else {
            throw new \Exception('Can only approve a submitted DIF');
        }
    }

    /**
     * Whether or not this DIF can be rejected.
     *
     * @return boolean True if this DIF can be rejected, False otherwise.
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
     * @throws \Exception When a DIF's status is anything other than unsubmitted.
     *
     * @return void
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
     * @return boolean True if this DIF can be unlocked, False otherwise.
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
     * @throws \Exception When a DIF's status is anything other than submitted or approved.
     *
     * @return void
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
     * @return boolean True if a DIF is locked, False otherwise.
     */
    public function isLocked()
    {
        // A DIF is locked if its status is anything other than unsubmitted.
        return self::STATUS_UNSUBMITTED !== $this->status;
    }

    /**
     * Set the DIF status.
     *
     * @param integer $status The status of this DIF.
     *
     * @return void
     */
    protected function setStatus($status)
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
}
