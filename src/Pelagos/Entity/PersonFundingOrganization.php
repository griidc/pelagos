<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Class to represent Person - Funding Organization associations.
 *
 * @Assert\UniqueEntity(
 *     fields={"person", "fundingOrganization"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Funding Organization"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_funding_organizations_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_funding_organizations_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorizationchecker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_funding_organizations_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorizationchecker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class PersonFundingOrganization extends Entity implements PersonAssociationInterface
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     *
     * @Exclude
     */
    protected static $properties = array(
        'person' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\Person',
            'entity' => 'Person',
            'setter' => 'setPerson',
            'getter' => 'getPerson',
        ),
        'fundingOrganization' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\FundingOrganization',
            'entity' => 'FundingOrganization',
            'setter' => 'setFundingOrganization',
            'getter' => 'getFundingOrganization',
        ),
        'role' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\FundingOrganizationRole',
            'entity' => 'FundingOrganizationRole',
            'setter' => 'setRole',
            'getter' => 'getRole',
        ),
        'label' => array(
            'type' => 'string',
            'getter' => 'getLabel',
            'setter' => 'setLabel',
        ),
    );

    /**
     * Person entity for this association.
     *
     * @var Person
     *
     * @Assert\NotBlank(
     *     message="Person is required"
     * )
     */
    protected $person;

    /**
     * Funding Organization entity for this association.
     *
     * @var FundingOrganization
     *
     * @Assert\NotBlank(
     *     message="Funding Organization is required"
     * )
     */
    protected $fundingOrganization;

    /**
     * Role for this association.
     *
     * @var FundingOrganizationRole
     *
     * @Assert\NotBlank(
     *     message="Role is required"
     * )
     */
    protected $role;

    /**
     * Label for this association.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Label is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Label cannot contain angle brackets (< or >)"
     * )
     */
    protected $label;

    /**
     * Setter for Person.
     *
     * @param Person|null $person The Person entity for this association.
     *
     * @return void
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;
    }

    /**
     * Getter for Person.
     *
     * @return Person|null The Person entity for this association.
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Setter for FundingOrganization.
     *
     * @param FundingOrganization|null $fundingOrganization The Funding Organization entity for this association.
     *
     * @return void
     */
    public function setFundingOrganization(FundingOrganization $fundingOrganization = null)
    {
        $this->fundingOrganization = $fundingOrganization;
    }

    /**
     * Getter for FundingOrganization.
     *
     * @return FundingOrganization|null The Funding Organization entity for this association.
     */
    public function getFundingOrganization()
    {
        return $this->fundingOrganization;
    }

    /**
     * Setter for Role.
     *
     * @param FundingOrganizationRole|null $role The Role for this association.
     *
     * @return void
     */
    public function setRole(FundingOrganizationRole $role = null)
    {
        $this->role = $role;
    }

    /**
     * Getter for Role.
     *
     * @return FundingOrganizationRole|null The Role for this association.
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Setter for Label.
     *
     * @param string|null $label The Label for this association.
     *
     * @throws \InvalidArgumentException When $label is not a string or null.
     *
     * @return void
     */
    public function setLabel($label)
    {
        if (is_string($label) or $label === null) {
            $this->label = $label;
        } else {
            throw new \InvalidArgumentException('Label must be a string or null, ' . gettype($label) . ' given');
        }
    }

    /**
     * Getter for Label.
     *
     * @return string|null The Label for this association.
     */
    public function getLabel()
    {
        return $this->label;
    }
}
