<?php

namespace App\Controller\UI;

use App\Handler\EntityHandler;
use App\Util\Geometry;
use App\Util\Metadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\InvalidGmlException;
use App\Entity\Dataset;
use App\Util\GmlUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The Dataset Monitoring controller.
 */
class DatalandController extends AbstractController
{
    /**
     * Geometry.
     *
     * @var Geometry
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
     *
     * @return Response
     */
    #[Route(path: '/data/v1/{udi}', name: 'pelagos_app_ui_dataland_v1')]
    public function defaultAction(string $udi, EntityManagerInterface $entityManager)
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
            $qb = $entityManager->createQueryBuilder();
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
     *
     * @return Response
     */
    #[Route(path: '/data/{udi}/metadata', name: 'pelagos_app_ui_dataland_metadata')]
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
     *
     * @return Response
     */
    #[Route(path: '/data/{udi}', name: 'pelagos_app_ui_dataland_default')]
    public function indexPage(string $udi, EntityManagerInterface $entityManager)
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
        $downloads = [];
        // Remotely hosted datasets are normally also hosted locally anyway, so including.
        if ($dataset->isAvailable()) {
            $qb = $entityManager->createQueryBuilder();
            $qb->select('a.creationTimeStamp')
                ->from('\App\Entity\LogActionItem', 'a')
                ->where('a.subjectEntityId = ?1')
                ->andwhere('a.subjectEntityName = ?2')
                ->andwhere('a.actionName = ?3')
                ->orderBy('a.creationTimeStamp', 'ASC')
                ->setParameter(1, $dataset->getId())
                ->setParameter(2, 'Pelagos\Entity\Dataset')
                ->setParameter(3, 'File Download');
            $query = $qb->getQuery();
            $downloads = $query->getResult();
        }

        // Setup variable to exist.
        $currentTimeStamp = 0;
        $downloadCount = 0;
        foreach ($downloads as $key => $timeStamp) {
            $dateTime = $timeStamp['creationTimeStamp'];
            $epochTime = (int) $dateTime->format('U');

            if ($key === array_key_first($downloads) or ($epochTime - $currentTimeStamp) > 30) {
                $currentTimeStamp = $epochTime;
                $downloadCount++;
            }
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
                'esri_api_key' => $mainsite = $this->getParameter('esri_api_key'),
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
     *
     * @return Response
     */
    #[Route(path: '/data/{udi}/formatted-metadata', name: 'pelagos_app_ui_dataland_formatted_metadata')]
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

    #[Route('/data/json/{dataset}', name: 'pelagos_app_ui_dataland_get_json', methods: ['GET', 'HEAD'])]
    public function getjson(Dataset $dataset, Geometry $geometryUtil): Response
    {
        $geoJson = '{}';
        $udi = $dataset->getUdi();
        $spatialExtent = $dataset->getSpatialExtentGeometry();

        if ($spatialExtent !== null) {
            $geoJson = $geometryUtil->convertGmlToGeoJSON(gml:$spatialExtent, udi:$udi, id:$udi);
        }

        return new JsonResponse(
            data: $geoJson,
            json: true
        );
    }
}
