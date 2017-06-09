<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Entity class to represent a Person to Data Repository Association.
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"person", "dataRepository"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Data Repository"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_data_repositories_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_data_repositories_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_data_repositories_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
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
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="personDataRepositories")
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
     * @ORM\ManyToOne(targetEntity="DataRepository", inversedBy="personDataRepositories")
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
     * @ORM\ManyToOne(targetEntity="DataRepositoryRole")
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
     * @ORM\Column(type="text")
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
