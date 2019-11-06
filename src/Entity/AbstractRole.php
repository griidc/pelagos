<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * Base entity class for the representation of typed Role classes.
 *
 * Classes of the type XxxRole can extend this base class.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractRole extends Entity implements RoleInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Role';

    /**
     * The name of this role.
     *
     * @var string
     *
     * @ORM\Column(type="citext")
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @CustomAssert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * The weight associated with this role.
     *
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(
     *     message="Weight is required"
     * )
     */
    protected $weight;

    /**
     * Setter for Name.
     *
     * @param string $name The name of this role.
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
     * @return string The name of this role.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for Weight.
     *
     * @param integer $weight The weight associated with this role.
     *
     * @throws \InvalidArgumentException When provided weight is not an integer or integer string.
     *
     * @return void
     */
    public function setWeight(int $weight)
    {
        if (is_int($weight) and $weight > 0 or
            is_string($weight) and ctype_digit($weight) and (integer) $weight > 0
        ) {
            $this->weight = (integer) $weight;
        } else {
            throw new \InvalidArgumentException('Weight must be a positive integer');
        }
    }

    /**
     * Getter for Weight.
     *
     * @return integer The weight associated with this role.
     */
    public function getWeight()
    {
        return $this->weight;
    }
}
