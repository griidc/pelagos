<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * Entity class to represent a Person to Data Repository Association.
 *
 *
 * @UniqueEntity(
 *     fields={"person", "dataRepository"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Data Repository"
 * )
 */
#[ORM\Entity]
class PersonDataRepository extends Entity implements PersonAssociationInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person to Data Repository Association';

    /**
     * Person entity for this association.
     *
     * @var Person
     *
     *
     * @Assert\NotBlank(
     *     message="Person is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'Person', inversedBy: 'personDataRepositories')]
    protected $person;

    /**
     * Data Repository entity for this association.
     *
     * @var DataRepository
     *
     *
     * @Assert\NotBlank(
     *     message="Data Repositry is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'DataRepository', inversedBy: 'personDataRepositories')]
    protected $dataRepository;

    /**
     * Role for this association.
     *
     * @var DataRepositoryRole
     *
     *
     * @Assert\NotBlank(
     *     message="Role is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'DataRepositoryRole')]
    protected $role;

    /**
     * Label for this association.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Label is required"
     * )
     */
    #[Assert\Regex('/<>/', 'Label cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text')]
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
