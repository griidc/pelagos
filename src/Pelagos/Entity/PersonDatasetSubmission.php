<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission association abstract class.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 */
abstract class PersonDatasetSubmission extends Entity
{
    /**
     * Valid values for self::$role.
     *
     * The array keys are the values to be set in self::role.
     */
    const ROLES = [
        'resourceProvider' => [
            'name' => 'Resource Provider',
            'description' => 'Party that supplies the resource.'
        ],
        'custodian' => [
            'name' => 'Custodian',
            'description' => 'Party that accepts accountability and responsibility for the data and ' .
                             'ensures appropriate care and maintenance of the resource.',
        ],
        'owner' => [
            'name' => 'Owner',
            'description' => 'Party that owns the resource.',
        ],
        'user' => [
            'name' => 'User',
            'description' => 'Party who uses the resource.',
        ],
        'distributor' => [
            'name' => 'Distributor',
            'description' => 'Party who distributes the resource.',
        ],
        'originator' => [
            'name' => 'Originator',
            'description' => 'Party who created the resource.',
        ],
        'pointOfContact' => [
            'name' => 'Point of Contact',
            'description' => 'Party who can be contacted for acquiring knowledge ' .
                             'about or acquisition of the resource.',
        ],
        'principalInvestigator' => [
            'name' => 'Principal Investigator',
            'description' => 'Key party responsible for gathering information and conducting research.',
        ],
        'processor' => [
            'name' => 'Processor',
            'description' => 'Party who has processed the data in a manner such that the resource has been modified.',
        ],
        'publisher' => [
            'name' => 'Publisher',
            'description' => 'Party who published the resource.',
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
     *
     * @ORM\ManyToOne(targetEntity="Person")
     */
    protected $person;

    /**
     * The role for this association.
     *
     * @var string
     *
     * @see ROLES class constant for possible values.
     *
     * @ORM\Column(type="text")
     */
    protected $role;

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
    public function setPerson(Person $person)
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
     * @param string $role The role for this association.
     *
     * @see ROLES class constant for possible values.
     *
     * @throws \InvalidArgumentException When $role is not a valid value.
     *
     * @return void
     */
    public function setRole($role)
    {
        if (!array_key_exists($role, static::ROLES)) {
            throw new \InvalidArgumentException("$role is not a valid value for PersonDatasetSubmission::\$role");
        }
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
}
