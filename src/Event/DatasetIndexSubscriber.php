<?php

namespace App\Event;

use App\Twig\Extensions as TwigExtentions;
use FOS\ElasticaBundle\Event\TransformEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use App\Entity\Dataset;
use App\Util\Geometry;

use App\Exception\InvalidGmlException;

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
            if ($dataset->getSpatialExtentGeometry()) {
                $wkt = $this->geometryUtil->convertGmlToWkt(
                    $dataset->getSpatialExtentGeometry()
                );
            }
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
            if ($dataset->getAcceptedDate() instanceof \DateTime) {
                $document->set('year', $dataset->getAcceptedDate()->format('Y'));
                $document->set('updatedDateTime', $dataset->getAcceptedDate()->format('Ymd\THis\Z'));
                $document->set('acceptedDate', $dataset->getAcceptedDate()->format('Y-m-d'));
            } else {
                $document->set('year', $dataset->getDatasetSubmission()->getModificationTimeStamp()->format('Y'));
                $document->set('updatedDateTime', $dataset->getDatasetSubmission()->getModificationTimeStamp()->format('Ymd\THis\Z'));
            }
            // Populate file size and format values
            $document->set('fileSize', $dataset->getDatasetSubmission()->getDatasetFileSize());
            if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                $document->set('fileSize', TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileColdStorageArchiveSize(), 2));
            } else {
                $document->set('fileSize', TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileSize(), 2));
            }
            $document->set('fileFormat', $dataset->getDatasetSubmission()->getDistributionFormatName());
            if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                $document->set('coldStorage', true);
            } else {
                $document->set('coldStorage', false);
            }
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

        if ($dataset->hasDatasetSubmission()) {
            if ($dataset->getDatasetSubmission()->getTemporalExtentBeginPosition() and $dataset->getDatasetSubmission()->getTemporalExtentEndPosition()) {
                $collectionStartDate = $dataset->getDatasetSubmission()->getTemporalExtentBeginPosition();
                $collectionEndDate = $dataset->getDatasetSubmission()->getTemporalExtentEndPosition();
                $document->set('collectionStartDate', $collectionStartDate->format('Y-m-d H:i:s'));
                $document->set('collectionEndDate', $collectionEndDate->format('Y-m-d H:i:s'));
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
