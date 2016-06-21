<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\DBALException;

/**
 * A new type of Case Insensitive Text, citext.
 */
class GeometryType extends \Jsor\Doctrine\PostGIS\Types\GeometryType
{
    /** 
     * Function to convert GML to Geometry.
     * 
     * @param string           $sqlExpr  The SQL.
     * @param AbstractPlatform $platform The DB Platform.
     * 
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('ST_GeomFromGML(%s, 4326)', $sqlExpr);
    }
}
