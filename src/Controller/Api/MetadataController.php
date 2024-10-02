<?php

namespace App\Controller\Api;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Dataset;
use App\Entity\DIF;
use App\Exception\InvalidGmlException;
use App\Util\Geometry;
use App\Util\Metadata;

/**
 * The Metadata api controller.
 */
class MetadataController extends EntityController
{
    /**
     * Get a single Metadata for a given id.
     *
     * @param Request  $request         The request object.
     * @param Geometry $geoUtil         Geometry Utility.
     * @param Metadata $metadataUtility Metadata Utility.
     *
     *
     *
     * @throws \Exception              When more than one dataset is found.
     * @throws NotFoundHttpException   When dataset is not found, or no metadata is available.
     * @throws BadRequestHttpException When the DIF is Unsubmitted.
     *
     * @Route(
     *     "/api/metadata",
     *     name="pelagos_api_metadata_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     *
     * @return Response
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    public function getAction(Request $request, Geometry $geoUtil, Metadata $metadataUtility)
    {
        $params = $request->query->all();
        $datasets = $this->entityHandler->getBy(Dataset::class, $params);

        if (count($datasets) > 1) {
            throw new \Exception('Found more than one Dataset');
        } elseif (count($datasets) == 0) {
            throw new NotFoundHttpException('Dataset Not Found');
        }

        $dataset = $datasets[0];

        if ($dataset->getIdentifiedStatus() != DIF::STATUS_APPROVED) {
            throw new BadRequestHttpException('DIF is not submitted');
        };
        $boundingBoxArray = array();
        $gml = $dataset->getDatasetSubmission()->getSpatialExtent();
        if ($gml) {
            try {
                $boundingBoxArray = $geoUtil->calculateGeographicBoundsFromGml($gml);
            } catch (InvalidGmlException $e) {
                $errors[] = $e->getMessage() . ' while attempting to calculate bonding box from gml';
                $boundingBoxArray = array();
            }
        }

        $generatedXmlMetadata = $metadataUtility->getXmlRepresentation($dataset, $boundingBoxArray);

        $response = new Response($generatedXmlMetadata);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
