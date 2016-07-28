<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

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
     * @return string WKT string for the envelope.
     */
    public function calculateEnvelopeFromGml($gml)
    {
        $sql = 'SELECT ST_AsText(ST_Envelope(ST_GeomFromGML(:gml, :srid)))';
        $connection = $this->entityManager->getConnection();
        $sth = $connection->prepare($sql);
        $sth->execute(array('gml' => $gml, 'srid' => 4326));
        $geom = $sth->fetchColumn();

        return $geom;
    }

    /**
     * Calculate the GeographicBoundingBox for a GML polygon.
     *
     * @param string $gml The GML polygon to calculate the bounding box for.
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
        $sth->execute(array('gml' => $gml));
        $geom = $sth->fetch(\PDO::FETCH_ASSOC);

        return $geom;
    }

    /**
     * Convert GML to WKT.
     *
     * @param string $gml The GML.
     *
     * @return string WKT string for the GML geometry.
     */
    public function convertGmlToWkt($gml)
    {
        $sql = 'SELECT ST_AsText(ST_GeomFromGML(:gml, :srid))';
        $connection = $this->entityManager->getConnection();
        $sth = $connection->prepare($sql);
        $sth->execute(array('gml' => $gml, 'srid' => 4326));
        $wkt = $sth->fetchColumn();
        return $wkt;
    }
}
