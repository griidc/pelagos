<?php

namespace Pelagos\Entity;

/**
 * Class to represent Person - Research Group associations.
 */
class PersonResearchGroup extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
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
     */
    protected $person;

    /**
     * Research Group entity for this association.
     *
     * @var ResearchGroup
     */
    protected $researchGroup;

    /**
     * Role for this association.
     *
     * @var ResearchGroupRole
     */
    protected $role;

    /**
     * Label for this association.
     *
     * @var string
     */
    protected $label;

    /**
     * Setter for Person.
     *
     * @param Person $person The Person entity for this association.
     *
     * @return void
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Getter for Person.
     *
     * @return Person The Person entity for this association.
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Setter for ResearchGroup.
     *
     * @param ResearchGroup $researchGroup The Research Group entity for this association.
     *
     * @return void
     */
    public function setResearchGroup(ResearchGroup $researchGroup)
    {
        $this->researchGroup = $researchGroup;
    }

    /**
     * Getter for ResearchGroup.
     *
     * @return ResearchGroup The Research Group entity for this association.
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * Setter for Role.
     *
     * @param ResearchGroupRole $role The Role for this association.
     *
     * @return void
     */
    public function setRole(ResearchGroupRole $role)
    {
        $this->role = $role;
    }

    /**
     * Getter for Role.
     *
     * @return ResearchGroupRole The Role for this association.
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Setter for Label.
     *
     * @param mixed $label The Label for this association.
     *
     * @return void
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Getter for Label.
     *
     * @return string The Label for this association.
     */
    public function getLabel()
    {
        return $this->label;
    }
}
