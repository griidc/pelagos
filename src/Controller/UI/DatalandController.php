<?php

namespace App\Controller\UI;

use App\Handler\EntityHandler;
use App\Util\DataStore;
use App\Util\Geometry;
use App\Util\Metadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @var Metadata
     */
    protected $metadataUtil;

    /**
     * Entity Handler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Base URL of Issue Tracker (Currenty JIRA)
     *
     * @var string
     */
    protected $issueTrackingBaseUrl;

    /**
     * Dataland Controller constructor.
     *
     * @param EntityHandler $entityHandler        The Entity Handler.
     * @param Geometry      $geoUtil              The Geomtery Util.
     * @param Metadata      $metadataUtil         The Metadata Util.
     * @param string        $issueTrackingBaseUrl The base URL for the GRIIDC issuetracker (Jira).
     */
    public function __construct(EntityHandler $entityHandler, Geometry $geoUtil, Metadata $metadataUtil, string $issueTrackingBaseUrl)
    {
        $this->entityHandler = $entityHandler;
        $this->geoUtil = $geoUtil;
        $this->metadataUtil = $metadataUtil;
        $this->issueTrackingBaseUrl = $issueTrackingBaseUrl;
    }

    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/data/v1/{udi}", name="pelagos_app_ui_dataland_v1")
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
            $rawXml = $this->metadataUtil->getXmlRepresentation($dataset, $boundingBoxArray);
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

        $response = new Response($this->metadataUtil
            ->getXmlRepresentation($dataset, $boundingBoxArray));
        $response->headers->set('Content-Type', 'text/xml');
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

    /**
     * The Dataland v2 Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/data/{udi}", name="pelagos_app_ui_dataland_default")
     *
     * @return Response
     */
    public function indexPage(string $udi)
    {
        $dataset = $this->getDataset($udi);

        $rawXml = null;
        $wkt = null;

        if ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED) {
            $boundingBoxArray = $this->getBoundingBox($dataset);
            $rawXml = $this->metadataUtil->getXmlRepresentation($dataset, $boundingBoxArray);
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
            'Dataland/v2/index.html.twig',
            array(
                'dataset' => $dataset,
                'downloads' => $downloadCount,
                'rawxml' => $rawXml,
                'wkt' => $wkt,
                'datasetSubmissionLockStatus' => true,
                'issuetracker' => $this->issueTrackingBaseUrl,
            )
        );
    }

    /**
     * Return the formatted metadata for a dataset as a file.
     *
     * @param string $udi The UDI of the dataset to return metadata for.
     *
     * @throws BadRequestHttpException When the dataset status is not accepted.
     *
     * @Route("/data/{udi}/formatted-metadata", name="pelagos_app_ui_dataland_formatted_metadata")
     *
     * @return Response
     */
    public function getFormattedMetadata(string $udi)
    {
        $dataset = $this->getDataset($udi);

        if ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_ACCEPTED) {
            throw new BadRequestHttpException('The dataset with udi ' . $udi . ' has not yet been accepted.');
        }

        $boundingBoxArray = $this->getBoundingBox($dataset);
        $rawXml = $this->metadataUtil->getXmlRepresentation($dataset, $boundingBoxArray);

        return $this->render(
            'Dataland/v2/formatted-metadata.html.twig',
            array(
                'rawxml' => $rawXml,
            )
        );
    }
}
