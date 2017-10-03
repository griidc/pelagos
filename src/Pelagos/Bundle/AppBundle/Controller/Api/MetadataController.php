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
     *     {"name"="someProperty", "dataType"="string", "required"=true, "description"="Filter by someProperty"},
     *     {"name"="forceSourceFromSubmission", "dataType"="boolean", "required"=false, "description"="Only generate MD from latest submission."}
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
     * @throws HttpException           When the XML can not be loaded from a file.
     * @throws NotFoundHttpException   When the metadata file is not found.
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $forceFromMetadata = false;
        $params = $request->query->all();
        if (isset($params['forceSourceFromSubmission'])) {
            if (1 == $params['forceSourceFromSubmission']) {
                $forceFromMetadata = true;
            }
            unset($params['forceSourceFromSubmission']);
        }

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

        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission == false) {
            $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact;
            $dif = $dataset->getDif();
            $datasetSubmission = new DatasetSubmission($dif, $personDatasetSubmissionDatasetContact);
            $datasetSubmission->submit($dif->getPrimaryPointOfContact());
            $dataset->setDatasetSubmission($datasetSubmission);
        }

        $metadata = $dataset->getMetadata();
        $metadataFilename = preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml';

        if (!$forceFromMetadata and $dataset->getMetadataStatus() == DatasetSubmission::METADATA_STATUS_ACCEPTED) {
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

        $tidyXml = new \tidy;
        $tidyXml->parseString(
            $xml,
            array(
                'input-xml' => true,
                'output-xml' => true,
                'indent' => true,
                'indent-spaces' => 4,
                'wrap' => 0,
            ),
            'utf8'
        );

        // Remove extra whitespace added around CDATA tags by tidy.
        $outXml = preg_replace('/>[\s]+<\!\[CDATA\[/', '><![CDATA[', $tidyXml);
        $outXml = preg_replace('/]]>\s+</', ']]><', $outXml);

        $response = new Response($outXml);
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $metadataFilename . '"');

        return $response;
    }
}
