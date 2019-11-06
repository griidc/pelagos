<?php

namespace App\Controller\UI;

use App\Handler\EntityHandler;
use App\Util\DataStore;
use App\Util\Geometry;
use App\Util\Metadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\Routing\Annotation\Route;

use App\Exception\InvalidGmlException;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Util\GmlUtil;

/**
 * The Dataset Monitoring controller.
 */
class DatalandController extends AbstractController
{
    /**
     * Geometry.
     *
     * @var geoUtil
     */
    protected $geoUtil;

    /**
     * Metadata.
     *
     * @var metadata
     */
    protected $metadata;

    /**
     * Entity Handler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Dataland Controller constructor.
     *
     * @param EntityHandler $entityHandler The Entity Handler.
     * @param Geometry      $geoUtil       The Geomtery Util.
     * @param Metadata      $metadata      The Metadata Util.
     */
    public function __construct(EntityHandler $entityHandler, Geometry $geoUtil, Metadata $metadata)
    {
        $this->entityHandler = $entityHandler;
        $this->geoUtil = $geoUtil;
        $this->metadata = $metadata;
    }

    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/data/{udi}", name="pelagos_app_ui_dataland_default")
     *
     * @return Response
     */
    public function defaultAction(string $udi)
    {
        $dataset = $this->getDataset($udi);

        $rawXml = null;
        $wkt = null;

        if ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED) {
            $boundingBoxArray = $this->getBoundingBox($dataset);
            $rawXml = $this->metadata->getXmlRepresentation($dataset, $boundingBoxArray);
        }
        //Logic to get DIF or Accepted Dataset is in Dataset Entity.
        if (!empty($dataset->getSpatialExtentGeometry())) {
            try {
                $wkt = $this->geoUtil->convertGmlToWkt($dataset->getSpatialExtentGeometry());
            } catch (InvalidGmlException $exception) {
                $wkt = null;
            }
        }

        $downloadCount = null;
        // Remotely hosted datasets are normally also hosted locally anyway, so including.
        if ($dataset->isAvailable()) {
            $qb = $this->get('doctrine')->getManager()->createQueryBuilder();
            $qb->select($qb->expr()->count('a'))
                ->from('\App\Entity\LogActionItem', 'a')
                ->where('a.subjectEntityId = ?1')
                ->andwhere('a.subjectEntityName = ?2')
                ->andwhere('a.actionName = ?3')
                ->setParameter(1, $dataset->getId())
                ->setParameter(2, 'Pelagos\Entity\Dataset')
                ->setParameter(3, 'File Download');
            $query = $qb->getQuery();
            $downloadCount = $query->getSingleScalarResult();
        }

        return $this->render(
            'Dataland/index.html.twig',
            $twigData = array(
                'dataset' => $dataset,
                'downloads' => $downloadCount,
                'rawxml' => $rawXml,
                'wkt' => $wkt,
            )
        );
    }

    /**
     * Return the metadata for a dataset as a file.
     *
     * @param string $udi The UDI of the dataset to return metadata for.
     *
     * @throws BadRequestHttpException When the dataset status is not accepted.
     *
     * @Route("/data/{udi}/metadata", name="pelagos_app_ui_dataland_metadata")
     *
     * @return Response
     */
    public function metadataAction(string $udi)
    {
        $dataset = $this->getDataset($udi);

        if ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_ACCEPTED) {
            throw new BadRequestHttpException('The dataset with udi ' . $udi . ' has not yet been accepted.');
        }

        $boundingBoxArray = $this->getBoundingBox($dataset);

        $filename = str_replace(':', '-', $udi) . '-metadata.xml';

        $response = new Response($this->metadata
            ->getXmlRepresentation($dataset, $boundingBoxArray));
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', "attachment; filename=$filename;");
        return $response;
    }

    /**
     * Download the dataset file.
     *
     * @param string    $udi       The UDI of the dataset to download.
     * @param DataStore $dataStore The data store.
     *
     * @throws \Exception When the dataset is marked as remotely hosted, but datasetFileUri does not contain a valid URL.
     *
     * @Route("/data/{udi}/download", name="pelagos_app_ui_dataland_download")
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(string $udi, DataStore $dataStore)
    {
        $dataset = $this->getDataset($udi);

        if ($dataset->getAvailabilityStatus()
            === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED) {
            if ($dataset->getDatasetSubmission() instanceof DatasetSubmission
                and null !== $dataset->getDatasetSubmission()->getDatasetFileUri()
                and preg_match('/^http/', $dataset->getDatasetSubmission()->getDatasetFileUri())) {
                return new RedirectResponse($dataset->getDatasetSubmission()->getDatasetFileUri());
            } else {
                throw new \Exception("Could not find valid url for remotely hosted dataset: $udi");
            }
        }

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException('You must be logged in to download dataset files');
        }

        if ($dataset->getAvailabilityStatus() !== DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) {
            throw $this->createAccessDeniedException('This dataset is not publicly available');
        }

        $fileInfo = $dataStore->getDownloadFileInfo($udi, 'dataset');

        $filename = null;

        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission) {
            $filename = $dataset->getDatasetSubmission()->getDatasetFileName();
        }

        if (null === $filename) {
            $filename = str_replace(':', '-', $fileInfo->getBaseName());
        }

        $response = new BinaryFileResponse($fileInfo->getRealPath());
        $response->headers->set('Content-Disposition', "attachment; filename=$filename;");
        return $response;
    }

    /**
     * Get the Dataset for an UDI.
     *
     * @param string $udi The UDI to get the dataset for.
     *
     * @throws NotFoundHttpException When no dataset is found with this UDI.
     * @throws \Exception            When more than one dataset is found with this UDI.
     *
     * @return Dataset
     */
    protected function getDataset(string $udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw new NotFoundHttpException("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        return $datasets[0];
    }

    /**
     * Get the bounding box for the dataset.
     *
     * @param Dataset $dataset The dataset for which the bounding box is generated.
     *
     * @return array
     */
    private function getBoundingBox(Dataset $dataset)
    {
        $spatialExtent = $dataset->getDatasetSubmission()->getSpatialExtent();
        $boundingBoxArray = array();
        if (!empty($spatialExtent)) {
            $gml = GmlUtil::addNamespace($dataset->getDatasetSubmission()->getSpatialExtent());
            if ($gml) {
                try {
                    $boundingBoxArray = $this->geoUtil->calculateGeographicBoundsFromGml($gml);
                } catch (InvalidGmlException $e) {
                    $errors[] = $e->getMessage() . ' while attempting to calculate bonding box from gml';
                    $boundingBoxArray = array();
                }
            }
        }
        return $boundingBoxArray;
    }
}
