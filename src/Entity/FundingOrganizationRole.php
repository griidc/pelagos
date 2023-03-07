<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to represent a Funding Organization Role.
 */
#[ORM\Entity]
class FundingOrganizationRole extends AbstractRole implements RoleInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Funding Organization Role';

    const LEADERSHIP = 'Leadership';
    const ADVISORY = 'Advisory';
    const ADMIN = 'Admin';
}
