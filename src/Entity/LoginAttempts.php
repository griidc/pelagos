<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to log a collection of Login Attemps.
 */
#[ORM\Entity]
class LoginAttempts
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Login Attempts';

    /**
     * Account this attempt is attached to.
     *
     * @var Account
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'Account', inversedBy: 'loginAttempts')]
    #[ORM\JoinColumn(referencedColumnName: 'person_id')]
    protected $account;

    /**
     * Constructor for Logins Attempt.
     *
     * @param Account $account The account to attach to this attempt.
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
