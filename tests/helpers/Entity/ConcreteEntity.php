<?php
/**
 * This file contains the implementation of the ConcreteEntity class.
 *
 * @package    Tests\Helpers
 * @subpackage ConcreteEntity
 */

namespace App\Tests\Helpers\Entity;

use App\Entity\Entity;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Concrete entity class for testing \Pelagos\Entity\Entity.
 */
class ConcreteEntity extends Entity
{
    /**
     * Name of the entity.
     *
     * @var string $name
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     */
    protected $name;

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'setter' => 'setName',
            'getter' => 'getName',
        ),
    );

    /**
     * Static method to get a list of properties for this class.
     *
     * @return array The list of properties for this class.
     */
    public static function getProperties()
    {
        return array_merge(parent::getProperties(), self::$properties);
    }

    /**
     * Setter for name.
     *
     * @param string $name Textual name of the entity.
     *
     * @access public
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name.
     *
     * @access public
     *
     * @return string String containing name of the entity.
     */
    public function getName()
    {
        return $this->name;
    }
}
