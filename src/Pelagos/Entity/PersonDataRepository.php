<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;

/**
 * Class to represent Person - Data Repository associations.
 *
 * @Assert\UniqueEntity(
 *     fields={"person", "dataRepository"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Data Repository"
 * )
 */
class PersonDataRepository extends Entity implements PersonAssociationInterface
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
        'dataRepository' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\DataRepository',
            'entity' => 'DataRepository',
            'setter' => 'setDataRepository',
            'getter' => 'getDataRepository',
        ),
        'role' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\DataRepositoryRole',
            'entity' => 'DataRepositoryRole',
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
     * Data Repository entity for this association.
     *
     * @var DataRepository
     *
     * @Assert\NotBlank(
     *     message="Data Repositry is required"
     * )
     */
    protected $dataRepository;

    /**
     * Role for this association.
     *
     * @var DataRepositoryRole
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
     * Setter for Data Repository.
     *
     * @param DataRepository|null $dataRepository The Data Repository  entity for this association.
     *
     * @return void
     */
    public function setDataRepository(DataRepository $dataRepository = null)
    {
        $this->dataRepository = $dataRepository;
    }

    /**
     * Getter for Data Repository.
     *
     * @return DataRepository|null The Data Repository entity for this association.
     */
    public function getDataRepository()
    {
        return $this->dataRepository;
    }

    /**
     * Setter for Role.
     *
     * @param DataRepositoryRole|null $role The Role for this association.
     *
     * @return void
     */
    public function setRole(DataRepositoryRole $role = null)
    {
        $this->role = $role;
    }

    /**
     * Getter for Role.
     *
     * @return DataRepositoryRole|null The Role for this association.
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
