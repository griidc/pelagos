<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;

/**
 * Class to represent Person - Research Group associations.
 *
 * @Assert\UniqueEntity(
 *     fields={"person", "researchGroup"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Research Group"
 * )
 */
class PersonResearchGroup extends Entity implements PersonAssociationInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person to Research Group Association';

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
        'researchGroup' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\ResearchGroup',
            'entity' => 'ResearchGroup',
            'setter' => 'setResearchGroup',
            'getter' => 'getResearchGroup',
        ),
        'role' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\ResearchGroupRole',
            'entity' => 'ResearchGroupRole',
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
     * Research Group entity for this association.
     *
     * @var ResearchGroup
     *
     * @Assert\NotBlank(
     *     message="Research Group is required"
     * )
     */
    protected $researchGroup;

    /**
     * Role for this association.
     *
     * @var ResearchGroupRole
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
     * Setter for ResearchGroup.
     *
     * @param ResearchGroup|null $researchGroup The Research Group entity for this association.
     *
     * @return void
     */
    public function setResearchGroup(ResearchGroup $researchGroup = null)
    {
        $this->researchGroup = $researchGroup;
    }

    /**
     * Getter for ResearchGroup.
     *
     * @return ResearchGroup|null The Research Group entity for this association.
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * Setter for Role.
     *
     * @param ResearchGroupRole|null $role The Role for this association.
     *
     * @return void
     */
    public function setRole(ResearchGroupRole $role = null)
    {
        $this->role = $role;
    }

    /**
     * Getter for Role.
     *
     * @return ResearchGroupRole|null The Role for this association.
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

    /**
     * Compare this PersonResearchGroupRoles Person, ResearchGroup and Role name with another's.
     *
     * @param Person        $person        A test Person object.
     * @param ResearchGroup $researchGroup A test ResearchGroup.
     * @param string        $roleName      The name of the test Role.
     *
     * @return bool Return true of all the arguments match the state of this object. False otherwise.
     */
    public function matches(Person $person, ResearchGroup $researchGroup, $roleName)
    {
        if ($this->getPerson()->isSameTypeAndId($person) &&
           $this->getResearchGroup()->isSameTypeAndId($researchGroup) &&
           $this->getRole()->getName() == $roleName) {
            return true;
        }
        return false;
    }
}
