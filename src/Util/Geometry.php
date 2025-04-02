<?php

namespace App\Util;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use App\Exception\InvalidGmlException;

/**
 * This is a utility class for dealing with envelope calculation.
 */
class Geometry
{
    /**
     * An instance of a Doctrine EntityManager class.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Memcached connection.
     *
     * @var string memcachedPort
     */
    private $memcachedPort;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param EntityManagerInterface $entityManager A Pelagos EntityManager.
     * @param string                 $memcachedPort The port for Memcached.
     */
    public function __construct(EntityManagerInterface $entityManager, string $memcachedPort)
    {
        $this->entityManager = $entityManager;
        $this->memcachedPort = $memcachedPort;
    }

    /**
     * Calculate the envelope for a GML polygon.
     *
     * @param string $gml The GML polygon to calculate the envelope for.
     *
     * @throws InvalidGmlException When PG Driver cannot process the supplied GML.
     *
     * @return string WKT string for the envelope.
     */
    public function calculateEnvelopeFromGml(string $gml)
    {
        $gml = GmlUtil::addNamespace($gml);

        $sql = 'SELECT ST_AsText(ST_Envelope(ST_GeomFromGML(:gml, :srid)))';
        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        try {
            $result = $statement->executeQuery(array('gml' => $gml, 'srid' => 4326));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $geom = $result->fetchFirstColumn();

        return $geom[0];
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
    public function calculateGeographicBoundsFromGml(string $gml)
    {
        $gml = GmlUtil::addNamespace($gml);

        $sql = 'SELECT
                    ST_XMin(ST_GeomFromGml(:gml)) as "westBoundLongitude",
                    ST_XMax(ST_GeomFromGml(:gml)) as "eastBoundLongitude",
                    ST_YMin(ST_GeomFromGml(:gml)) as "southBoundLatitude",
                    ST_YMax(ST_GeomFromGml(:gml)) as "northBoundLatitude"';

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        try {
            $result = $statement->executeQuery(array('gml' => $gml));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $geom = $result->fetchAssociative();

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
    public function convertGmlToWkt(string $gml)
    {
        $gml = GmlUtil::addNamespace($gml);

        $sql = 'SELECT ST_AsText(ST_GeomFromGML(:gml, :srid))';
        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        try {
            $result = $statement->executeQuery(array('gml' => $gml, 'srid' => 4326));
        } catch (DriverException $e) {
            if (preg_match('/unknown spatial reference system/', $e->getMessage())) {
                $err = 'unknown spatial reference system in GML';
            } else {
                $err = 'unknown GML error';
            }
            throw new InvalidGmlException($err);
        }
        $wkt = $result->fetchFirstColumn();

        return $wkt[0];
    }

    /**
     * Convert GML to GeoJSON representation
     *
     * @param string $gml Textual GML of geometry.
     * @param string $udi Dataset UDI.
     * @param string $id  Identifier for feature.
     */
    public function convertGmlToGeoJSON(string $gml, string $udi = 'unknown', string $id = 'A'): mixed
    {
        if ($udi !== 'unknown') {
            $memcached = MemcachedAdapter::createConnection('memcached://localhost:' . $this->memcachedPort);
            $geoJson = $memcached->get('gml2geojson' . $udi);
            if ($geoJson) {
                return $geoJson;
            }
        }

        $sql = "SELECT ST_AsGeoJSON(t.*) FROM (VALUES(:id, :name, ST_GeomFromGML(:gml))) AS t(id, name, geom)";
        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        try {
            $result = $statement->executeQuery(array('gml' => $gml, 'id' => $id, 'name' => $udi));
        } catch (DriverException $e) {
            throw new InvalidGmlException($e->getMessage());
        }
        $geoJson = $result->fetchOne();
        // don't cache for 'unknown' UDI, or if no GeoJSON was returned
        if ($udi !== 'unknown' || $geoJson !== false) {
            /** @psalm-suppress PossiblyUndefinedVariable */ //can't be undedfined here because of the above check
            $memcached->set('gml2geojson' . $udi, $geoJson);
        }
        return $geoJson;
    }
}
