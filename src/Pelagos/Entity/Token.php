<?php
/**
 * This file contains the implementation of the Token entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Token
 */

namespace Pelagos\Entity;

/**
 * Class to represent a token.
 */
class Token extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * Used by common update code.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'tokenText' => array(
            'type' => 'string',
            'setter' => 'setTokenText',
            'getter' => 'getTokenText'
        )
    );

    /**
     * Token's identifying text string.
     *
     * @var string $tokenText
     *
     * @Assert\NotBlank(
     *     message="Token text is required."
     * )
     */
    protected $tokenText;

    /**
     * Getter for tokenText property.
     *
     * @return string Tokentext to identify a Token entity.
     */
    public function getTokenText()
    {
        return $this->tokenText;
    }

    /**
     * Setter for tokenText property.
     *
     * @param string $tokenText Tokentext to identify a Token entity.
     *
     * @access public
     *
     * @return void
     */
    public function setTokenText($tokenText)
    {
        $this->tokenText = $tokenText;
    }
}
