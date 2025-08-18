<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission association abstract class.
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr')]
abstract class PersonDatasetSubmission
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * Valid values for self::$role.
     *
     * The array keys are the values to be set in self::role.
     */
    const ROLES = [
        'pointOfContact' => [
            'name' => 'Point of Contact',
            'description' => 'Party who can be contacted for acquiring knowledge ' .
                             'about or acquisition of the resource.',
        ],
        'principalInvestigator' => [
            'name' => 'Principal Investigator',
            'description' => 'Key party responsible for gathering information and conducting research.',
        ],
        'author' => [
            'name' => 'Author',
            'description' => 'Party who authored the resource.',
        ],
    ];

    /**
     * The person for this association.
     *
     * @var Person
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    protected $person;

    /**
     * The role for this association.
     *
     * @var string
     *
     * @see ROLES class constant for possible values.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $role;

    /**
     * Whether this entity is a primary contact, or not.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $primaryContact;

    /**
     * Get the valid choices for role.
     *
     * @return array
     */
    public static function getRoleChoices()
    {
        return array_flip(
            array_map(
                function ($role) {
                    return $role['name'];
                },
                static::ROLES
            )
        );
    }

    /**
     * Setter for person.
     *
     * @param Person $person The Person for this association.
     *
     * @return void
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;
    }

    /**
     * Getter for person.
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Setter for role.
     *
     * @param string|null $role The role for this association.
     *
     * @see ROLES class constant for possible values.
     *
     * @throws \InvalidArgumentException When $role is not a valid value.
     *
     * @return void
     */
    public function setRole(?string $role = null)
    {
        if (!array_key_exists($role, static::ROLES) and $role !== null) {
            throw new \InvalidArgumentException("$role is not a valid value for PersonDatasetSubmission::\$role");
        }
        $this->role = $role;
    }

    /**
     * Getter for role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Settter for datasetSubmission.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission for this association.
     *
     * @return void
     */
    public function setDatasetSubmission(DatasetSubmission $datasetSubmission)
    {
        $this->datasetSubmission = $datasetSubmission;
    }

    /**
     * Getter for datasetSubmission.
     *
     * @return DatasetSubmission
     */
    public function getDatasetSubmission()
    {
        return $this->datasetSubmission;
    }

    /**
     * Getter for primary contact indicator.
     *
     * @return boolean
     */
    public function isPrimaryContact()
    {
        return $this->primaryContact;
    }

    /**
     * Setter for primary contact indicator.
     *
     * @param boolean|null $state A boolean to be set, true if primary contact, false otherwise.
     *
     * @return void
     */
    public function setPrimaryContact(?bool $state)
    {
        $this->primaryContact = $state;
    }
}
