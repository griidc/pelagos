<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * A Class for Research Group Roles.
 */
class ResearchGroupRole extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'weight' => array(
            'type' => 'integer',
            'getter' => 'getWeight',
            'setter' => 'setWeight',
        ),
    );

    /**
     * The name of this role.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * The weight associated with this role.
     *
     * @var integer
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
    public function setName($name)
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
    public function setWeight($weight)
    {
        if (is_int($weight) and $weight > 0 or ctype_digit($weight) and (integer) $weight > 0) {
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
