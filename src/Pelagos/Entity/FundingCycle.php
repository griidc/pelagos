<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

use JMS\Serializer\Annotation as Serializer;

use Pelagos\Exception\NotDeletableException;

/**
 * Entity class to represent a Funding Cycle.
 *
 * @ORM\Entity
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
 * @Assert\CompareProperties(
 *     left="endDate",
 *     comparison="GreaterThan",
 *     right="startDate",
 *     message="End Date must be after Start Date"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_cycles_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_cycles_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorizationchecker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_cycles_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorizationchecker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
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
     * @ORM\Column(type="citext")
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * Description of a funding cycle.
     *
     * @var string $description
     *
     * @access protected
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Funding cycle's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Funding cycle's start date.
     *
     * @var \Datetime $startDate
     *
     * @access protected
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     *
     * @access protected
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     *
     * @access protected
     *
     * @ORM\ManyToOne(targetEntity="FundingOrganization", inversedBy="fundingCycles")
     *
     * @Assert\NotBlank(
     *     message="Funding Organization is required"
     * )
     *
     * @Serializer\MaxDepth(1)
     */
    protected $fundingOrganization;

    /**
     * Funding cycle's list of associated research groups.
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="ResearchGroup", mappedBy="fundingCycle")
     *
     * @ORM\OrderBy({"name" = "ASC"})
     *
     * @Serializer\MaxDepth(2)
     */
    protected $researchGroups;

    /**
     * Funding cycle's UDI Prefix.
     *
     * @var string $udiPrefix
     *
     * @access protected
     *
     * @ORM\Column(nullable=true)
     */
    protected $udiPrefix;

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding cycle.
     *
     * @access public
     *
     * @return void
     */
    public function setName($name)
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
     * @param string $description Description of funding cycle.
     *
     * @access public
     *
     * @return void
     */
    public function setDescription($description)
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
     * @param string $url Funding organization's Website URL.
     *
     * @access public
     *
     * @return void
     */
    public function setUrl($url)
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
     * Setter for udiPrefix.
     *
     * @param string $udiPrefix The prefix of the UDI for this Funding Cycle.
     *
     * @access public
     *
     * @return void
     */
    public function setUdiPrefix($udiPrefix)
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
