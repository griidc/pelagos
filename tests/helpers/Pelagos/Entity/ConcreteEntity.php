<?php
/**
 * This file contains the implementation of the ConcreteEntity class.
 *
 * @package    Tests\Helpers
 * @subpackage ConcreteEntity
 */

namespace Pelagos\Entity;

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
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     */
    protected $name;

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

    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     *
     * @return ConcreteEntity Return the updated object.
     */
    public function update(array $updates)
    {
        parent::update($updates);
        foreach ($updates as $field => $value) {
            switch($field) {
                case 'name':
                    $this->setName($value);
                    break;
            }
        }
        return $this;
    }
}
