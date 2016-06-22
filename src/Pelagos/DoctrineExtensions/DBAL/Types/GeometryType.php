<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * A Geometry type, that accepts GML.
 */
class GeometryType extends \Jsor\Doctrine\PostGIS\Types\GeometryType
{
    /**
     * Function to convert GML to Geometry.
     *
     * @param string           $sqlExpr  The SQL.
     * @param AbstractPlatform $platform The DB Platform.
     *
     * @return string The sql string for a geometry.
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('ST_GeomFromGML(%s, 4326)', $sqlExpr);
    }
}
