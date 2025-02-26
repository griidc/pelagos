<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to represent a Research Group Role.
 */
#[ORM\Entity]
class ResearchGroupRole extends AbstractRole implements RoleInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Research Group Role';

    // Known Reseach Group Roles.
    const LEADERSHIP = 'Leadership';
    const ADMIN = 'Administration';
    const DATA = 'Data';
    const RESEARCHER = 'Researcher';

    public const array ROLES = [
        self::LEADERSHIP => self::LEADERSHIP,
        self::ADMIN => self::ADMIN,
        self::DATA => self::DATA,
        self::RESEARCHER => self::RESEARCHER,
    ];
}
