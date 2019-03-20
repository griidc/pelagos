<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to log a collection of Login Attemps.
 *
 * @ORM\Entity
 */
class LoginAttempts extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Login Attempts';

    /**
     * Account this attempt is attached to.
     *
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="loginAttempts")
     *
     * @ORM\JoinColumn(referencedColumnName="person_id")
     */
    protected $account;

    /**
     * Constructor for Logins Attempt.
     *
     * @param Account   $account   The account to attach to this attempt.
     * @param \DateTime $timeStamp The time stamp for the attempt.
     */
    public function __construct(Account $account, \DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            $this->attemptTimeStamp = $timeStamp;
        } else {
            $this->attemptTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        $this->account = $account;
    }
}
