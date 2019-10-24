<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class to represent a Research Group Role.
 *
 * @ORM\Entity
 */
class ResearchGroupRole extends AbstractRole implements RoleInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Research Group Role';
}
