<?php

namespace App\Entity;

use App\Enum\DatasetLifecycleStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use JMS\Serializer\Annotation as Serializer;
use App\Exception\NotDeletableException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entity class to represent a Funding Cycle.
 *
 *
 * @Assert\GroupSequence({
 *     "id",
 *     "unique_id",
 *     "FundingCycle",
 *     "Entity",
 * })
 *
 * @UniqueEntity(
 *     fields={"fundingOrganization","name"},
 *     errorPath="name",
 *     message="Name must be unique within a FundingOrganization"
 * )
 *
 * @UniqueEntity(
 *     fields={"udiPrefix"},
 *     message="This UDI prefix is already used."
 * )
 */
#[ORM\Entity]
class FundingCycle extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Funding Cycle';

    /**
     * Name of a funding cycle.
     *
     * @var string $name
     *
     * @access protected
     *
     * @Serializer\Groups({"organization"})
     *
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'citext')]
    protected $name;

    /**
     * Description of a funding cycle.
     *
     * @var string $description
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * Funding cycle's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     *
     * @CustomAssert\NoAngleBrackets(
     *     message="URL cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Funding cycle's start date.
     *
     * @var \Datetime $startDate
     *
     * @access protected
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     *
     * @access protected
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     *
     * @access protected
     *
     * @Serializer\Groups({"organization"})
     *
     *
     * @Assert\NotBlank(
     *     message="Funding Organization is required"
     * )
     * @Serializer\MaxDepth(1)
     */
    #[ORM\ManyToOne(targetEntity: 'FundingOrganization', inversedBy: 'fundingCycles')]
    protected $fundingOrganization;

    /**
     * Funding cycle's list of associated research groups.
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @access protected
     *
     *
     *
     * @Serializer\MaxDepth(2)
     */
    #[ORM\OneToMany(targetEntity: 'ResearchGroup', mappedBy: 'fundingCycle')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    protected $researchGroups;

    /**
     * Funding cycle's UDI Prefix.
     *
     * @var string $udiPrefix
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="UDI Prefix is required"
     * )
     * @Assert\Regex(
     *      pattern="/^[A-Z\d]{2}$/",
     *      message="Funding Cycle prefix must be composed of 2 uppercase characters or numbers."
     * )
     */
    #[ORM\Column(type: 'text', nullable: false)]
    protected $udiPrefix;

    /**
     * This holds the position in the sort order of this Entity.
     *
     * @var integer
     *
     *
     * @Assert\Range(
     *     min = 1,
     *     max = 2147483647,
     *     notInRangeMessage = "Sort position must be in between 1 and 2147483647",
     *     invalidMessage = "Sort position must be a positive integer."
     * )
     */
    #[ORM\Column(nullable: true, type: 'integer')]
    protected $sortOrder;

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding cycle.
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
     * @return string String containing name of funding cycle.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description Description of funding cycle.
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
     * @return string Description of funding cycle.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for Funding Cycle.
     *
     * @param FundingOrganization $fundingOrg The funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingOrganization(FundingOrganization $fundingOrg = null)
    {
        $this->fundingOrganization = $fundingOrg;
    }

    /**
     * Getter for Funding Organization.
     *
     * @access public
     *
     * @return FundingOrganization Funding Organization.
     */
    public function getFundingOrganization()
    {
        return $this->fundingOrganization;
    }

    /**
     * Setter for url.
     *
     * @param string|null $url Funding organization's Website URL.
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
     * @return string URL of funding cycle's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for startDate.
     *
     * @param \DateTime $startDate The Start Date.
     *
     * @access public
     *
     * @return void
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    /**
     * Getter for startDate.
     *
     * @access public
     *
     * @return \DateTime startDate of funding cycle's Website.
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Setter for endDate.
     *
     * @param \DateTime $endDate The End Date.
     *
     * @access public
     *
     * @return void
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    /**
     * Getter for endDate.
     *
     * @access public
     *
     * @return \DateTime endDate of funding cycle's Website.
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Getter for getResearchGroups.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection List of funding cycle's research groups.
     */
    public function getResearchGroups()
    {
        return $this->researchGroups;
    }

    /**
     * Return a collection of all Datasets for the Funding Cycle.
     */
    public function getDatasets(): Collection
    {
        $datasets = new ArrayCollection();
        foreach ($this->getResearchGroups() as $researchGroup) {
            /** @var ResearchGroup $researchGroup */
            foreach ($researchGroup->getDatasets() as $dataset) {
                $datasets->add($dataset);
            }
        }
        return $datasets;
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
     * Setter for udiPrefix.
     *
     * @param string|null $udiPrefix The prefix of the UDI for this Funding Cycle.
     *
     * @access public
     *
     * @return void
     */
    public function setUdiPrefix(?string $udiPrefix)
    {
        $this->udiPrefix = $udiPrefix;
    }

    /**
     * Getter for udiPrefix.
     *
     * @access public
     *
     * @return string String containing UDI prefix of funding cycle.
     */
    public function getUdiPrefix()
    {
        return $this->udiPrefix;
    }

    /**
     * Setter for sortOrder.
     *
     * @param integer|null $position The position to set in the sort ordering.
     *
     * @access public
     *
     * @return void
     */
    public function setSortOrder(?int $position)
    {
        $this->sortOrder = $position;
    }

    /**
     * Getter for sortOrder.
     *
     * @access public
     *
     * @return integer Of position to use in a sorted list.
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Check if this FundingCycle is deletable.
     *
     * This method throws a NotDeletableException when the FundingCycle has associated
     * ResearchGroups. The NotDeletableException will have its reasons set to a list of
     * reasons the FundingCycle is not deletable.
     *
     * @throws NotDeletableException When the FundingCycle has associated ResearchGroups.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
        $researchGroupCount = count($this->getResearchGroups());
        if ($researchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($researchGroupCount > 1 ? 'are' : 'is') .
                " $researchGroupCount associated Research Group" .
                ($researchGroupCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }
}
