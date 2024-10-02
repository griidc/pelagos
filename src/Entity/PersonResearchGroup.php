<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Entity class to represent a Person to Research Group Association.
 *
 *
 * @UniqueEntity(
 *     fields={"person", "researchGroup"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Research Group"
 * )
 *
 */
#[ORM\Entity(repositoryClass: 'App\Repository\PersonResearchGroupRepository')]
class PersonResearchGroup extends Entity implements PersonAssociationInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person to Research Group Association';

    /**
     * Person entity for this association.
     *
     * @var Person
     *
     *
     *
     * @Assert\NotBlank(
     *     message="Person is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'Person', inversedBy: 'personResearchGroups')]
    #[Serializer\Groups(['person'])]
    #[Serializer\MaxDepth(2)]
    protected $person;

    /**
     * Research Group entity for this association.
     *
     * @var ResearchGroup
     *
     *
     * @Assert\NotBlank(
     *     message="Research Group is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'ResearchGroup', inversedBy: 'personResearchGroups')]
    protected $researchGroup;

    /**
     * Role for this association.
     *
     * @var ResearchGroupRole
     *
     *
     * @Assert\NotBlank(
     *     message="Role is required"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'ResearchGroupRole')]
    protected $role;

    /**
     * Label for this association.
     *
     * @var string
     *
     *
     *
     * @Assert\NotBlank(
     *     message="Label is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Label cannot contain angle brackets (< or >)"
     * )
     */
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['person'])]
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
    public function setLabel(?string $label)
    {
        $this->label = $label;
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
