<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Pelagos\Exception\InvalidGmlException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Bundle\AppBundle\Form\MdappType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Metadata;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;

/**
 * The Metadata api controller.
 */
class MetadataController extends EntityController
{
    /**
     * Get a count of Metadata.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Metadata",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Metadata",
     *       "data_class": "Pelagos\Entity\Metadata"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Metadata was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/count")
     *
     * @Rest\View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(Metadata::class, $request);
    }

    /**
     * Get a single Metadata for a given id.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Metadata",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=true, "description"="Filter by someProperty"}
     *   },
     *   output = "XML",
     *   statusCodes = {
     *     200 = "The requested Metadata was successfully retrieved.",
     *     415 = "String could not be parsed as XML.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @throws \Exception              When more than one dataset is found.
     * @throws NotFoundHttpException   When dataset is not found, or no metadata is available.
     * @throws BadRequestHttpException When the DIF is Unsubmitted.
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        // We need this utility service to get the metadata in ISO 19115-2 formatted XML.
        $metadataUtility = $this->container->get('pelagos.util.metadata');

        $params = $request->query->all();
        $datasets = $this->container->get('pelagos.entity.handler')->getBy(Dataset::class, $params);

        if (count($datasets) > 1) {
            throw new \Exception('Found more than one Dataset');
        } elseif (count($datasets) == 0) {
            throw new NotFoundHttpException('Dataset Not Found');
        }

        $dataset = $datasets[0];

        if ($dataset->getIdentifiedStatus() != DIF::STATUS_APPROVED) {
            throw new BadRequestHttpException('DIF is not submitted');
        };
        $geoUtil = $this->get('pelagos.util.geometry');
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
        $metadataFilename = preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml';

        $response = new Response($generatedXmlMetadata);
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $metadataFilename . '"');

        return $response;
    }
}
