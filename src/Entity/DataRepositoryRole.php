<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to represent a Data Repository Role.
 */
#[ORM\Entity]
class DataRepositoryRole extends AbstractRole implements RoleInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Data Repository Role';

    // Known Data Repository Roles.
    const MANAGER = 'Manager';
    const ENGINEER = 'Engineer';
    const SUPPORT = 'Support';
    const SME = 'Subject Matter Expert';
}
