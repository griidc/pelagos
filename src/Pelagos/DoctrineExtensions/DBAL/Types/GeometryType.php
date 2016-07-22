<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * A Geometry type, that accepts GML.
 */
class GeometryType extends \Jsor\Doctrine\PostGIS\Types\GeometryType
{
    /**
     * Method to convert a geometry in the database to a Well-Known Text (WKT) string.
     *
     * @param string $sqlExpr  The SQL.
     * @param mixed  $platform The database platform.
     *
     * @return string A Well-Known Text (WKT) representation of the geometry.
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        // ::geometry type cast needed for 1.5
        return sprintf('ST_AsText(%s::geometry)', $sqlExpr);
    }

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
