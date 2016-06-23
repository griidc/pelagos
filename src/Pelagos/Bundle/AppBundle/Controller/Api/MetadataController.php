<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

/**
 * The Metadata api controller.
 */
class MetadataController extends EntityController
{
    /**
     * Get a single Metadata for a given id.
     *
     * @param integer $id The id of the Metadata to return.
     *
     * @ApiDoc(
     *   section = "Metadata",
     *   output = "XML",
     *   statusCodes = {
     *     200 = "The requested Metadata was successfully retrieved.",
     *     404 = "The requested Metadata was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response
     */
    public function getAction($id)
    {
        $dataset = $this->handleGetOne(Dataset::class, $id);
        $metadata = $dataset->getMetadata();

        if ($dataset->getMetadataStatus() == DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $dataset->getMetadata()->getXml() instanceof \SimpleXMLElement
            ) {
            $xml = $metadata->getXml()->asXML();
        } else {
            $xml = $this->get('twig')->render(
                'PelagosAppBundle:MetadataGenerator:MI_Metadata.xml.twig',
                array(
                    'dataset' => $dataset,
                    'metadataFilename' => preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml',
                    )
            );
        }

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
