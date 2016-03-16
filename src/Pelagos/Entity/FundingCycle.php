<?php
/**
 * This file contains the implementation of the FundingCycle entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage FundingCycle
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \Pelagos\Exception\NotDeletableException;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Class to represent funding cycles.
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
     */
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     *
     * @access protected
     */
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     *
     * @access protected
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
     * @Serializer\MaxDepth(2)
     */
    protected $researchGroups;

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     *
     * @Serializer\Exclude
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'setter' => 'setName',
            'getter' => 'getName',
        ),
        'description' => array(
            'type' => 'string',
            'setter' => 'setDescription',
            'getter' => 'getDescription',
        ),
        'url' => array(
            'type' => 'string',
            'setter' => 'setUrl',
            'getter' => 'geturl',
        ),
        'startDate' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'resolver' => 'resolveDate',
            'setter' => 'setStartDate',
            'getter' => 'getStartDate',
            'serializer' => 'serializeDate',
        ),
        'endDate' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'resolver' => 'resolveDate',
            'setter' => 'setEndDate',
            'getter' => 'getEndDate',
            'serializer' => 'serializeDate',
        ),
        'fundingOrganization' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\FundingOrganization',
            'entity' => 'FundingOrganization',
            'setter' => 'setFundingOrganization',
            'getter' => 'getFundingOrganization',
        ),
        'researchGroups' => array(
            'type' => 'object',
            'class' => 'Doctrine\Common\Collections\Collection',
            'getter' => 'getResearchGroups',
            'serialize' => false,
        ),
    );

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
