<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base entity class for the representation of typed Role classes.
 *
 * Classes of the type XxxRole can extend this base class.
 */
#[ORM\MappedSuperclass]
abstract class AbstractRole extends Entity implements RoleInterface
{
    use IdTrait;

    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Role';

    /**
     * The name of this role.
     *
     * @var string
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Name cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'citext')]
    #[Assert\NotBlank(message: 'Name is required')]
    protected $name;

    /**
     * The weight associated with this role.
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank(message: 'Weight is required')]
    protected $weight;

    /**
     * Setter for Name.
     *
     * @param string $name the name of this role
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Getter for Name.
     *
     * @return string the name of this role
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for Weight.
     *
     * @param int $weight the weight associated with this role
     *
     * @return void
     *
     * @throws \InvalidArgumentException when provided weight is not an integer or integer string
     */
    public function setWeight(int $weight)
    {
        if ($weight <= 0) {
            throw new \InvalidArgumentException('Weight must be a positive integer');
        }

        $this->weight = $weight;
    }

    /**
     * Getter for Weight.
     *
     * @return int the weight associated with this role
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Return the name of this role.
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
