<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Entity class to represent a Person to Research Group Association.
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"person", "researchGroup"},
 *     errorPath="person",
 *     message="A Person can have only one association with a Research Group"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_research_groups_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_research_groups_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_person_research_groups_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
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
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="personResearchGroups")
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
     * @ORM\ManyToOne(targetEntity="ResearchGroup", inversedBy="personResearchGroups")
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
     * @ORM\ManyToOne(targetEntity="ResearchGroupRole")
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
}
