<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Exception\InvalidGmlException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;
use Pelagos\Util\GmlUtil;

/**
 * The Dataset Monitoring controller.
 *
 * @Route("/data")
 */
class DatalandController extends UIController
{
    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/{udi}")
     *
     * @return Response
     */
    public function defaultAction($udi)
    {
        $dataset = $this->getDataset($udi);

        $geometryUtil = $this->get('pelagos.util.geometry');

        $rawXml = null;
        $wkt = null;

        if ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED) {
            $boundingBoxArray = $this->getBoundingBox($dataset);
            $rawXml = $this->get('pelagos.util.metadata')->getXmlRepresentation($dataset, $boundingBoxArray);
        }
        //Logic to get DIF or Accepted Dataset is in Dataset Entity.
        try {
            $wkt = $geometryUtil->convertGmlToWkt($dataset->getSpatialExtentGeometry());
        } catch (InvalidGmlException $exception) {
            $wkt = null;
        }

        $downloadCount = null;
        // Remotely hosted datasets are normally also hosted locally anyway, so including.
        if (in_array(
            $dataset->getAvailabilityStatus(),
            array(
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED
            )
        )
        ) {
            $qb = $this->get('doctrine')->getManager()->createQueryBuilder();
            $qb->select($qb->expr()->count('a'))
                ->from('\Pelagos\Entity\LogActionItem', 'a')
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
            'PelagosAppBundle:Dataland:index.html.twig',
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
     * @Route("/{udi}/metadata")
     *
     * @return Response
     */
    public function metadataAction($udi)
    {
        $dataset = $this->getDataset($udi);

        if ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_ACCEPTED) {
            throw new BadRequestHttpException('The dataset with udi ' . $udi . ' has not yet been accepted.');
        }

        $boundingBoxArray = $this->getBoundingBox($dataset);

        $filename = str_replace(':', '-', $udi) . '-metadata.xml';

        $response = new Response($this->get('pelagos.util.metadata')
            ->getXmlRepresentation($dataset, $boundingBoxArray));
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', "attachment; filename=$filename;");
        return $response;
    }

    /**
     * Download the dataset file.
     *
     * @param string $udi The UDI of the dataset to download.
     *
     * @throws \Exception            When the dataset is marked as remotely hosted,
     *                               but datasetFileUri does not contain a valid URL.
     * @throws AccessDeniedException When the user is not authenticated.
     * @throws AccessDeniedException When the dataset is not publicly available.
     *
     * @Route("/{udi}/download")
     *
     * @return BinaryFileResponse
     */
    public function downloadAction($udi)
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

        $fileInfo = $this->get('pelagos.util.data_store')->getDownloadFileInfo($udi, 'dataset');

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
    protected function getDataset($udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw $this->createNotFoundException("No dataset found for UDI: $udi");
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
        $geoUtil = $this->get('pelagos.util.geometry');
        $gml = GmlUtil::addNamespace($dataset->getDatasetSubmission()->getSpatialExtent());
        $boundingBoxArray = array();
        if ($gml) {
            try {
                $boundingBoxArray = $geoUtil->calculateGeographicBoundsFromGml($gml);
            } catch (InvalidGmlException $e) {
                $errors[] = $e->getMessage() . ' while attempting to calculate bonding box from gml';
                $boundingBoxArray = array();
            }
        }
        return $boundingBoxArray;
    }
}
