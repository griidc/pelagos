<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * Entity class to represent a Person to Funding Organization Association.
 *
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['person', 'fundingOrganization'], errorPath: 'person', message: 'A Person can have only one association with a Funding Organization')]
class PersonFundingOrganization extends Entity implements PersonAssociationInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person to Funding Organization Association';

    /**
     * Person entity for this association.
     *
     * @var Person
     *
     */
    #[ORM\ManyToOne(targetEntity: 'Person', inversedBy: 'personFundingOrganizations')]
    #[Assert\NotBlank(message: 'Person is required')]
    protected $person;

    /**
     * Funding Organization entity for this association.
     *
     * @var FundingOrganization
     *
     */
    #[ORM\ManyToOne(targetEntity: FundingOrganization::class, inversedBy: 'personFundingOrganizations')]
    #[Assert\NotBlank(message: 'Funding Organization is required')]
    protected $fundingOrganization;

    /**
     * Role for this association.
     *
     * @var FundingOrganizationRole
     *
     */
    #[ORM\ManyToOne(targetEntity: 'FundingOrganizationRole')]
    #[Assert\NotBlank(message: 'Role is required')]
    protected $role;

    /**
     * Label for this association.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Label cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Label is required')]
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

    public function __toString(): string
    {
        return ($this->getPerson()?->getFullName() ?? '') . ' - ' . ($this->getFundingOrganization()?->getName() ?? '');
    }
}
