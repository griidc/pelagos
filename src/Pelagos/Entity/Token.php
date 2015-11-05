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
        'token' => array(
            'type' => 'string',
            'setter' => 'setToken',
            'getter' => 'getToken'
        )
    );

    /**
     * Token's token text string.
     *
     * @var string $token
     *
     * @Assert\NotBlank(
     *     message="Token text is required."
     * )
     */
    protected $token;

    /**
     * Getter for token property.
     *
     * @return string Token text-based token to identify a Token entity.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Setter for token property.
     *
     * @param string $token Token text-based token to identify a Token entity.
     *
     * @access public
     *
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}
