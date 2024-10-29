<?php

namespace App\Entity;

use App\Enum\DatasetLifecycleStatus;
use App\Exception\NotDeletableException;
use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity class to represent a Funding Cycle.
 *
 *
 */
#[ORM\Entity]
#[Assert\GroupSequence(['id', 'unique_id', 'FundingCycle', 'Entity'])]
#[UniqueEntity(fields: ['fundingOrganization', 'name'], errorPath: 'name', message: 'Name must be unique within a FundingOrganization')]
#[UniqueEntity(fields: ['udiPrefix'], message: 'This UDI prefix is already used.')]
class FundingCycle extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Funding Cycle';

    /**
     * Name of a funding cycle.
     *
     * @var string $name
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext')]
    #[Serializer\Groups(['organization'])]
    #[Assert\NotBlank(message: 'Name is required')]
    protected $name;

    /**
     * Description of a funding cycle.
     *
     * @var string $description
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Description cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * Funding cycle's Website url.
     *
     * @var string $url
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'URL cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $url;

    /**
     * Funding cycle's start date.
     *
     * @var \Datetime $startDate
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     */
    #[ORM\Column(type: 'date', nullable: true)]
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     *
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'FundingOrganization', inversedBy: 'fundingCycles')]
    #[Serializer\Groups(['organization'])]
    #[Serializer\MaxDepth(1)]
    #[Assert\NotBlank(message: 'Funding Organization is required')]
    protected $fundingOrganization;

    /**
     * Funding cycle's list of associated research groups.
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\OneToMany(targetEntity: 'ResearchGroup', mappedBy: 'fundingCycle')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Serializer\MaxDepth(2)]
    protected $researchGroups;

    /**
     * Funding cycle's UDI Prefix.
     *
     * @var string $udiPrefix
     *
     *
     */
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: 'UDI Prefix is required')]
    #[Assert\Regex(pattern: '/^[A-Z\d]{2}$/', message: 'Funding Cycle prefix must be composed of 2 uppercase characters or numbers.')]
    protected $udiPrefix;

    /**
     * This holds the position in the sort order of this Entity.
     *
     * @var int
     */
    #[ORM\Column(nullable: true, type: 'integer')]
    #[Assert\Range(min: 1, max: 2147483647, notInRangeMessage: 'Sort position must be in between 1 and 2147483647', invalidMessage: 'Sort position must be a positive integer.')]
    protected $sortOrder;

    /**
     * Setter for name.
     *
     * @param string $name textual name of funding cycle
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
     * @return string string containing name of funding cycle
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description description of funding cycle
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
     * @return string description of funding cycle
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for Funding Cycle.
     *
     * @param FundingOrganization $fundingOrg the funding organization
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
     * @return FundingOrganization funding Organization
     */
    public function getFundingOrganization()
    {
        return $this->fundingOrganization;
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
     * @return string URL of funding cycle's Website
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for startDate.
     *
     * @param \DateTime $startDate the Start Date
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
     * @return \DateTime startDate of funding cycle's Website
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Setter for endDate.
     *
     * @param \DateTime $endDate the End Date
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
     * @return \DateTime endDate of funding cycle's Website
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Getter for getResearchGroups.
     *
     * @return \Doctrine\Common\Collections\Collection list of funding cycle's research groups
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
     * Check the see if this Funding Cycle has any Research Groups without Datasets.
     */
    public function hasResearchGroupsWithoutDatasets(): bool
    {
        $result = false;

        foreach ($this->getResearchGroups() as $researchGroup) {
            if (0 == count($researchGroup->getDatasets())) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Setter for udiPrefix.
     *
     * @param string|null $udiPrefix the prefix of the UDI for this Funding Cycle
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
     * @return string string containing UDI prefix of funding cycle
     */
    public function getUdiPrefix()
    {
        return $this->udiPrefix;
    }

    /**
     * Setter for sortOrder.
     *
     * @param int|null $position the position to set in the sort ordering
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
     * @return int of position to use in a sorted list
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
     * @return void
     *
     * @throws NotDeletableException when the FundingCycle has associated ResearchGroups
     */
    public function checkDeletable()
    {
        $notDeletableReasons = [];
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
