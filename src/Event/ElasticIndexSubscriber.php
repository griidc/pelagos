<?php

namespace App\Event;

use App\Entity\Dataset;
use App\Entity\InformationProduct;
use App\Exception\InvalidGmlException;
use App\Twig\Extensions as TwigExtentions;
use App\Util\Geometry;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber for events related to the dataset index.
 */
class ElasticIndexSubscriber implements EventSubscriberInterface
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
     * Populate calculated fields in the Elastic index.
     *
     * @param PostTransformEvent $event The event that triggeref this.
     *
     * @return void
     */
    public function populateCalculatedFields(PostTransformEvent $event)
    {
        $entity = $event->getObject();
        if ($entity instanceof Dataset) {
            $this->populateDatasetAttributes($event);
        } else if ($entity instanceof InformationProduct) {
            $this->populateInformationProductAttributes($event);
        }
        return;
    }

    /**
     * Populate dataset attributes into elastic index.
     *
     * @param PostTransformEvent $event The event that triggered this.
     *
     * @return void
     */
    private function populateDatasetAttributes(PostTransformEvent $event)
    {
        $document = $event->getDocument();
        $index = $document->getIndex();
        $dataset = $event->getObject();
        $wkt = null;
        $geoJson = null;



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

        try {
            if ($dataset->getSpatialExtentGeometry()) {
                $geoJson = $this->geometryUtil->convertGmlToGeoJSON(
                    gml: $dataset->getSpatialExtentGeometry(),
                    udi: $dataset->getUdi(),
                    id: $dataset->getUdi(),
                );
            }
        } catch (InvalidGmlException $exception) {
            $geoJson = null;
        }

        if (null !== $wkt) {
            $document->set('geometry', $wkt);
            $geometry = \geoPHP::load($wkt, 'wkt');
            $simpleGeometry = $geometry->simplify(0.00001);
            // If the geometry couldn't be simplified.
            if (null == $simpleGeometry or $simpleGeometry->isEmpty()) {
                // Set the original geometry as a GeoJSON array.
                $json = $geometry->out('json');
            } else {
                // Set the simpllified geometry as a GeoJSON array.
                $json = $simpleGeometry->out('json');
            }
            $array = json_decode($json, true);

            if (key_exists('coordinates', $array)) {
                $array['coordinates'] = array_map(array($this, 'coordinatesToFloat'), $array['coordinates']);
            }

            $document->set('simpleGeometry', $array);
        }

        if ($index == 'search_pelagos') {
            $document->set('datasetLifecycleStatus', $dataset->getDatasetLifecycleStatus());
        }

        if (null !== $geoJson and $index === 'search_pelagos') {
            $document->set('geometry', $geoJson);
            $geometry = \geoPHP::load($geoJson, 'json');
            $simpleGeometry = $geometry->simplify(0.00001);
            // If the geometry couldn't be simplified.
            if (null == $simpleGeometry or $simpleGeometry->isEmpty()) {
                // Set the original geometry as a GeoJSON array.
                $json = $geometry->out('json');
            } else {
                // Set the simpllified geometry as a GeoJSON array.
                $json = $simpleGeometry->out('json');
            }

            // Although the geometry is already a JSON string, we need to decode it to
            // the input containts a Feature, while geoPHP just outputs a geometry.

            $array = json_decode($json, true);

            if (key_exists('coordinates', $array)) {
                $array['coordinates'] = array_map(array($this, 'coordinatesToFloat'), $array['coordinates']);
            }

            $document->set('simpleGeometry', $array);
        }

        if (null !== $dataset->getDatasetSubmission()) {
            if ($dataset->getAcceptedDate() instanceof \DateTime) {
                $document->set('year', $dataset->getAcceptedDate()->format('Y'));
                $document->set('updatedDateTime', $dataset->getAcceptedDate()->format('Ymd\THis\Z'));
                $document->set('acceptedDate', $dataset->getAcceptedDate()->format('Y-m-d'));
                $document->set('publishedDate', $dataset->getAcceptedDate()->format('Y-m-d'));
            } else {
                $document->set('year', $dataset->getDatasetSubmission()->getModificationTimeStamp()->format('Y'));
                $document->set('updatedDateTime', $dataset->getDatasetSubmission()->getModificationTimeStamp()->format('Ymd\THis\Z'));
                $document->set('publishedDate', $dataset->getDatasetSubmission()->getSubmissionTimeStamp()->format('Y-m-d'));
            }
            $document->set('tags', $dataset->getTags());
            // Populate file size and format values
            $document->set('fileSize', $dataset->getDatasetSubmission()->getDatasetFileSize());
            if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                $document->set('fileSize', TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getColdStorageTotalUnpackedSize(), 2));
            } else {
                $document->set('fileSize', TwigExtentions::formatBytes($dataset->getDatasetSubmission()->getDatasetFileSize(), 2));
            }
            $document->set('fileFormat', $dataset->getDatasetSubmission()->getDistributionFormatName());
            if ($dataset->getDatasetSubmission()->isDatasetFileInColdStorage()) {
                $document->set('coldStorage', true);
            } else {
                $document->set('coldStorage', false);
            }
            if ($dataset->getDatasetSubmission()->getTemporalExtentBeginPosition() and $dataset->getDatasetSubmission()->getTemporalExtentEndPosition()) {
                $collectionStartDate = $dataset->getDatasetSubmission()->getTemporalExtentBeginPosition();
                $collectionEndDate = $dataset->getDatasetSubmission()->getTemporalExtentEndPosition();
                $document->set('collectionStartDate', $collectionStartDate->format('Y-m-d H:i:s'));
                $document->set('collectionEndDate', $collectionEndDate->format('Y-m-d H:i:s'));
            }
        } elseif ($dataset->hasDif()) {
            if ($dataset->getDif()->getModificationTimeStamp() instanceof \DateTime) {
                $document->set('updatedDateTime', $dataset->getDif()->getModificationTimeStamp()->format('Ymd\THis\Z'));
            }
            if (null !== $dataset->getDif()->getEstimatedStartDate()) {
                $document->set('estimatedStartDate', $dataset->getDif()->getEstimatedStartDate()->format('Y-m-d'));
            }
            if (null !== $dataset->getDif()->getEstimatedEndDate()) {
                $document->set('estimatedEndDate', $dataset->getDif()->getEstimatedEndDate()->format('Y-m-d'));
            }
            if ($dataset->getDif()->getApprovedDate() instanceof \DateTime) {
                $document->set('publishedDate', $dataset->getDif()->getApprovedDate()->format('Y-m-d'));
            }
        } else {
            $document->set('updatedDateTime', $dataset->getModificationTimeStamp()->format('Ymd\THis\Z'));
        }
    }

    /**
     * Populate info product attributes into elastic index.
     *
     * @param PostTransformEvent $event
     *
     * @return void
     */
    private function populateInformationProductAttributes(PostTransformEvent $event)
    {
        $document = $event->getDocument();
        /**@var InformationProduct */
        $infoProduct = $event->getObject();

        $document->set('publishedDate', $infoProduct->getModificationTimeStamp()->format('Y-m-d'));
    }


    /**
     * Forces array elements to float.
     *
     * @param array|float $coordinates
     */
    private function coordinatesToFloat($coordinates): array|float
    {
        if (!is_array($coordinates)) {
            return floatval($coordinates);
        }
        $floatCoordinates = [];
        foreach ($coordinates as $pair) {
            if (!is_array($pair)) {
                $floatCoordinates[] = floatval($pair);
            } else {
                $floatCoordinates[] = $this->coordinatesToFloat($pair);
            }
        }

        return $floatCoordinates;
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
            PostTransformEvent::class => 'populateCalculatedFields',
        );
    }
}
