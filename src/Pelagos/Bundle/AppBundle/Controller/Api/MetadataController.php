<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

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

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;
use Pelagos\Bundle\AppBundle\Form\MdappType;

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
     * @throws BadRequestHttpException When the Dataset Submission is Unsubmitted.
     * @throws HttpException           When the XML can not be loaded from a file.
     * @throws NotFoundHttpException   When the metadata file is not found.
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $datasets = $this->container->get('pelagos.entity.handler')->getBy(Dataset::class, $request->query->all());

        if (count($datasets) > 1) {
            throw new \Exception('Found more than one Dataset');
        } elseif (count($datasets) == 0) {
            throw new NotFoundHttpException('Dataset Not Found');
        }

        $dataset = $datasets[0];

        if ($dataset->getDatasetSubmissionStatus() == DatasetSubmission::STATUS_UNSUBMITTED) {
            throw new BadRequestHttpException('Dataset is not submitted');
        }

        $metadata = $dataset->getMetadata();
        $metadataFilename = preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml';

        if ($dataset->getMetadataStatus() == DatasetSubmission::METADATA_STATUS_ACCEPTED) {
            if ($dataset->getMetadata() instanceof Metadata and
                $dataset->getMetadata()->getXml() instanceof \SimpleXMLElement
            ) {
                $xml = $metadata->getXml()->asXML();
            } else {
                try {
                    $fileInfo = $this
                        ->get('pelagos.util.data_store')
                        ->getDownloadFileInfo($dataset->getUdi(), 'metadata');
                } catch (FileNotFoundException $e) {
                    throw new NotFoundHttpException($e->getMessage());
                }
                try {
                    $xmlDoc = new \SimpleXMLElement($fileInfo->getRealPath(), null, true);
                    $xml = $xmlDoc->asXML();
                } catch (\Exception $e) {
                    throw new HttpException(415, $e->getMessage());
                }
            }
        } else {
            $xml = $this->get('twig')->render(
                'PelagosAppBundle:MetadataGenerator:MI_Metadata.xml.twig',
                array(
                    'dataset' => $dataset,
                    'metadataFilename' => $metadataFilename,
                    )
            );
        }

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $metadataFilename . '"');

        return $response;
    }
}
