<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to log Login Attemps.
 *
 * @ORM\Entity
 */
class Logins extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Logins'; // A tribute to Kenny.

    /**
     * User's ID.
     *
     * @var string
     *
     * @ORM\Column(type="citext", nullable=false, unique=false)
     */
    protected $username;
    
    /**
     * IP Address.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $ipAddress;
    
    /**
     * The time stamp (in UTC).
     *
     * @var \DateTime $timeStamp;
     *
     * @ORM\Column(type="datetimetz")
     */
    protected $timeStamp;
    
    /**
     * Constructor for Logins.
     *
     * @param string    $username  The user name for the attempt.
     * @param string    $ipAddress The IP for the attempt.
     * @param \DateTime $timeStamp The time stamp for the attempt.
     *
     */
    public function __construct($username, $ipAddress, \DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            $this->timeStamp = $timeStamp;
        } else {
            $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $this->username = $username;
        $this->ipAddress = $ipAddress;
    }
} 
    