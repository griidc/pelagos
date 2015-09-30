<?php

namespace Pelagos\Entity;

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
     */
    protected $name;

    /**
     * The weight associated with this role.
     *
     * @var integer
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
        if (!is_int($weight) and !ctype_digit($weight)) {
            throw new \InvalidArgumentException('Weight must be an integer');
        }
        $this->weight = (integer) $weight;
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
