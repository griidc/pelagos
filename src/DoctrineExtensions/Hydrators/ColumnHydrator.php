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
     * Hydrate data using PDO::FETCH_COLUMN.
     *
     * @return array Result set.
     */
    protected function hydrateAllData()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
