<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

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

        $rawXml = null;

        if ($dataset->getMetadata() instanceof Metadata
            and $dataset->getMetadata()->getXml() instanceof \SimpleXMLElement) {
            $rawXml = $dataset->getMetadata()->getXml()->asXml();
        }

        return $this->render(
            'PelagosAppBundle:Dataland:index.html.twig',
            $twigData = array(
                'dataset' => $dataset,
                'rawxml' => $rawXml,
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
