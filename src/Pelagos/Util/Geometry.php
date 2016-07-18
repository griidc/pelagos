<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Jsor\Doctrine\PostGIS\Event\ORMSchemaEventSubscriber;

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
     * @var geom A geometry.
     * @var envelope An envelope.
     * @var wkt A well known text.
     *
     * @return string WKT string for the envelope.
     */
    public function calculateEnvelopeFromGml($gml)
    {
        $query = $this->entityManager->createQuery('SELECT ST_GeomFromGML(:gml)');
        $query->setParameter('gml', $gml);
        $geom = $query->getResult();

        $query = $this->entityManager->createQuery('SELECT ST_Envelope(:geom)');
        $query->setParameter('geom', $geom);
        $envelope = $query->getResult();

        $query = $this->entityManager->createQuery('ST_AsText(:envelope)');
        $query->setParameter('envelope', $envelope);
        $wkt = $query->getResult();

        return $wkt;
    }

    /**
     * Calculate the GeographicBoundingBox for a GML polygon.
     *
     * @param string $gml The GML polygon to calculate the bounding box for.
     *
     * @return string XML String (gmd:EX_GeographicBoundingBox).
     */
    public function calculateGeographicBoundingBoxFromGml($gml)
    {
        $boundingBox = '';
        return $bondingBox;
    }
}
