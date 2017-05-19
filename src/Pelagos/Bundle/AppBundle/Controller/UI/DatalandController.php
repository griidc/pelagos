<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

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

use Pelagos\Util\ISOMetadataExtractorUtil;

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

        if ($dataset->getMetadata() instanceof Metadata
            and $dataset->getMetadata()->getXml() instanceof \SimpleXMLElement) {
            $rawXml = $dataset->getMetadata()->getXml()->asXml();
        }

        if ($dataset->getDif() instanceof DIF) {
            $wkt = $geometryUtil->convertGmlToWkt(
                $dataset
                ->getDif()
                ->getSpatialExtentGeometry()
            );
        }

        if ($dataset->getMetadata() instanceof Metadata) {
            $wkt = $dataset->getMetadata()->getGeometry();
        }

        $datasetSubmission = $dataset->getDatasetSubmission();

        // If we have approved Metadata, load contact into datasetSubmission.
        if ($dataset->getDatasetSubmission()->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED) {
            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $dataset->getMetadata()->getXml(),
                $datasetSubmission,
                $this->getDoctrine()->getManager()
            );
        }

        return $this->render(
            'PelagosAppBundle:Dataland:index.html.twig',
            $twigData = array(
                'dataset' => $dataset,
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
     * @throws NotFoundHttpException   When the dataset does not have metadata.
     * @throws BadRequestHttpException When the metadata for the dataset has not been accepted.
     *
     * @Route("/{udi}/metadata")
     *
     * @return Response
     */
    public function metadataAction($udi)
    {
        $dataset = $this->getDataset($udi);

        if (!$dataset->getMetadata() instanceof Metadata
            or !$dataset->getMetadata()->getXml() instanceof \SimpleXMLElement) {
            throw $this->createNotFoundException("No metadata found for dataset with UDI: $udi");
        }

        if ($dataset->getMetadataStatus() !== DatasetSubmission::METADATA_STATUS_ACCEPTED) {
            throw new BadRequestHttpException("The metadata has not yet been accepted for dataset with UDI: $udi");
        }

        $filename = str_replace(':', '-', $udi) . '-metadata.xml';

        $response = new Response($dataset->getMetadata()->getXml()->asXml());
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
}
