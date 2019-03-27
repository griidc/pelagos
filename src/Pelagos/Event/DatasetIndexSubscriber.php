<?php

namespace Pelagos\Event;

use FOS\ElasticaBundle\Event\TransformEvent;
use FOS\ElasticaBundle\Event\TypePopulateEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Util\Geometry;

use Pelagos\Exception\InvalidGmlException;

/**
 * An event subscriber for events related to the dataset index.
 */
class DatasetIndexSubscriber implements EventSubscriberInterface
{
    /**
     * The Geometry utility service.
     *
     * @var Geometry
     */
    protected $geometryUtil;

    /**
     * Constructor.
     *
     * @param Geometry $geometryUtil The Geometry utility service.
     */
    public function __construct(Geometry $geometryUtil)
    {
        $this->geometryUtil = $geometryUtil;
    }

    /**
     * Populate calculated fields in the Dataset index.
     *
     * @param TransformEvent $event The event that triggeref this.
     *
     * @return void
     */
    public function populateCalculatedFields(TransformEvent $event)
    {
        $dataset = $event->getObject();

        if (!$dataset instanceof Dataset) {
            return;
        }

        $document = $event->getDocument();

        $wkt = null;

        // Logic to get the spatialExtent is in Dataset Entity.
        try {
            $wkt = $this->geometryUtil->convertGmlToWkt(
                $dataset->getSpatialExtentGeometry()
            );
        } catch (InvalidGmlException $exception) {
            $wkt = null;
        }


        if (null !== $wkt) {
            $document->set('geometry', $wkt);
            $geometry = \geoPHP::load($wkt, 'wkt');
            $simpleGeometry = $geometry->simplify(0.1);
            // If the geometry couldn't be simplified.
            if (null == $simpleGeometry or $simpleGeometry->isEmpty()) {
                // Set the original geometry as a GeoJSON array.
                $document->set('simpleGeometry', json_decode($geometry->out('json'), true));
            } else {
                // Set the simpllified geometry as a GeoJSON array.
                $document->set('simpleGeometry', json_decode($simpleGeometry->out('json'), true));
            }
        }

        if (null !== $dataset->getDatasetSubmission()) {
            $document->set('year', $dataset->getDatasetSubmission()->getCreationTimeStamp()->format('Y'));
            $document->set('updatedDateTime', $dataset->getDatasetSubmission()->getCreationTimeStamp()->format('Ymd\THis\Z'));
        } elseif ($dataset->hasDif()) {
            $document->set('updatedDateTime', $dataset->getDif()->getModificationTimeStamp()->format('Ymd\THis\Z'));
        } else {
            $document->set('updatedDateTime', $dataset->getModificationTimeStamp()->format('Ymd\THis\Z'));
        }

        if ($dataset->hasDif()) {
            if (null !== $dataset->getDif()->getEstimatedStartDate()) {
                $document->set('estimatedStartDate', $dataset->getDif()->getEstimatedStartDate()->format('Y-m-d'));
            }
            if (null !== $dataset->getDif()->getEstimatedEndDate()) {
                $document->set('estimatedEndDate', $dataset->getDif()->getEstimatedEndDate()->format('Y-m-d'));
            }
        }
    }

    /**
     * Get a list of the events this subscriber is subscribed to.
     *
     * @see EventSubscriberInterface::getSubscribedEvents
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'populateCalculatedFields',
        );
    }
}
