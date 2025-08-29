<?php

namespace App\DoctrineExtensions\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * A custom hydrator to return a singler column of results.
 *
 * @see AbstractHydrator
 */
class ColumnHydrator extends AbstractHydrator
{
    /**
     *
     * @return array Result set.
     */
    protected function hydrateAllData(): array
    {
        return $this->statement()->fetchFirstColumn();
    }
}
