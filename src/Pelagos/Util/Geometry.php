<?php
namespace Pelagos\Util;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManager;
use Pelagos\Exception\InvalidGmlException;

/**
 * This is a utility class for dealing with envelope calculation.
 */
class Geometry
{
    /**
     * An instance of a Doctrine EntityManager class.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param EntityManager $entityManager A Pelagos EntityManager.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Calculate the envelope for a GML polygon.
     *
     * @param string $gml The GML polygon to calculate the envelope for.
     *
     * @throws InvalidGmlException When PG Driver cannot process the supplied GML.
     *
     * @return string WKT string for the envelope or null if bad gml.
     */
    public function calculateEnvelopeFromGml($gml)
    {
        $sql = 'SELECT ST_AsText(ST_Envelope(ST_GeomFromGML(:gml, :srid)))';
        $connection = $this->entityManager->getConnection();
        $sth = $connection->prepare($sql);
        try {
            $sth->execute(array('gml' => $gml, 'srid' => 4326));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $geom = $sth->fetchColumn();

        return $geom;
    }

    /**
     * Calculate the GeographicBoundingBox for a GML polygon.
     *
     * @param string $gml The GML polygon to calculate the bounding box for.
     *
     * @throws InvalidGmlException When PG Driver cannot process the supplied GML.
     *
     * @return array Of North, South, East, West.
     */
    public function calculateGeographicBoundsFromGml($gml)
    {
        $sql = 'SELECT
                    ST_XMin(ST_GeomFromGml(:gml)) as "westBoundLongitude",
                    ST_XMax(ST_GeomFromGml(:gml)) as "eastBoundLongitude",
                    ST_YMin(ST_GeomFromGml(:gml)) as "southBoundLatitude",
                    ST_YMax(ST_GeomFromGml(:gml)) as "northBoundLatitude"';

        $connection = $this->entityManager->getConnection();
        $sth = $connection->prepare($sql);
        try {
            $sth->execute(array('gml' => $gml));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $geom = $sth->fetch(\PDO::FETCH_ASSOC);

        return $geom;
    }

    /**
     * Convert GML to WKT.
     *
     * @param string $gml The GML.
     *
     * @throws InvalidGmlException When PG Driver cannot process the supplied GML.
     *
     * @return string WKT string for the GML geometry.
     */
    public function convertGmlToWkt($gml)
    {
        $sql = 'SELECT ST_AsText(ST_GeomFromGML(:gml, :srid))';
        $connection = $this->entityManager->getConnection();
        $sth = $connection->prepare($sql);
        try {
            $sth->execute(array('gml' => $gml, 'srid' => 4326));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $wkt = $sth->fetchColumn();
        return $wkt;
    }
}
