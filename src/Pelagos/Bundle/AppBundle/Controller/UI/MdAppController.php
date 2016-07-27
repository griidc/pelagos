<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\MdappType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Doctrine\ORM\Query;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

/**
 * The MDApp controller.
 *
 * @Route("/mdapp")
 */
class MdAppController extends UIController
{
    /**
     * MDApp UI.
     *
     * @Route("")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->renderUi();
    }

    /**
     * Download original user-submitted raw metadata file.
     *
     * @param integer $id The Pelagos ID of the metadata's associated dataset.
     *
     * @Route("/download-orig-raw-xml/{id}")
     *
     * @return XML|string
     */
    public function downloadMetadataFromOriginalFile($id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $dataStoreUtil = $this->get('pelagos.util.data_store');

        $dataset = $entityHandler->get(Dataset::class, $id);
        if (null === $dataset) {
            $response = new Response('This dataset could not be found.');
            return $response;
        }

        $metadataFile = $dataStoreUtil->getDownloadFileInfo($dataset->getUdi(), 'metadata');

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (null === $datasetSubmission) {
            $response = new Response('Could not find dataset submission for dataset.');
            return $response;
        }

        $originalMetadataFilename = $datasetSubmission->getMetadataFileName();
        if (null === $originalMetadataFilename) {
            $response = new Response('No metadata filename found in dataset submission.');
            return $response;
        }

        $metadataFilePathName = $metadataFile->getRealPath();
        if (false === $metadataFilePathName) {
            $response = new Response("File $metadataFilePathName not available.");
            return $response;
        } else {
            $response = new BinaryFileResponse($metadataFilePathName);
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $originalMetadataFilename . ';');
            return $response;
        }
    }

    /**
     * Download metadata from persistance.
     *
     * @param string $id The ID of Dataset associated with desired Metadata.
     *
     * @Route("/download-db-xml/{id}")
     *
     * @throws \Exception If SimpleXml not extractable from Metadata.
     * @throws \Exception If conversion to string from SimpleXml fails.
     * @return XML|string
     */
    public function downloadMetadataFromDB($id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');

        // Since ID is passed via URL, this could happen via end user action.
        $dataset = $entityHandler->get(Dataset::class, $id);
        if (null === $dataset) {
            $response = new Response('This dataset could not be found.');
            return $response;
        }

        // This could also happen via end-user action if manually entering values on URL.
        $metadata = $dataset->getMetadata();
        if (null === $metadata) {
            $response = new Response('This dataset has no Metadata in database.');
            return $response;
        }

        // This isn't likely, but included for robustness.
        $metadataSimpleXml = $metadata->getXml();
        if (null === $metadataSimpleXml) {
            throw new \Exception("Could not retrieve SimpleXML object from Metadata for Dataset ID: $id");
        }

        // This really isn't likely, but included for robustness.
        $metadataXml = $metadataSimpleXml->asXml();
        if (false === $metadataXml) {
            throw new \Exception("Could not convert SimpleXML into string representation for: $id");
        }

        $windowsFilenameSafeUdi = str_replace(':', '-', $dataset->getUdi());
        $response = new Response($metadataXml);
        $response->headers->set('Content-Disposition', 'attachment; filename='
            . $windowsFilenameSafeUdi . '-metadata.xml;');
        return $response;
    }

    /**
     * Change the metadata status.
     *
     * @param Request $request The Symfony request object.
     * @param integer $id      The id of the Dataset to change the metadata status for.
     *
     * @Route("/change-metadata-status/{id}")
     * @Method("POST")
     *
     * @return Response
     */
    public function changeMetadataStatusAction(Request $request, $id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $mdappLogger = $this->get('pelagos.util.mdapplogger');
        $dataset = $entityHandler->get(Dataset::class, $id);
        $from = $dataset->getMetadataStatus();
        $udi = $dataset->getUdi();
        $to = $request->request->get('to');
        if (null !== $to) {
            $dataset->getDatasetSubmission()->setMetadataStatus($to);
            $entityHandler->update($dataset);
            $mdappLogger->writeLog($this->getUser()->getUsername() . " has changed metadata status for $udi ($from -> $to)");
        }
        return $this->renderUi();
    }

    /**
     * Render the UI for MDApp.
     *
     * @return Response
     */
    protected function renderUi()
    {
        // If not DRPM, show Access Denied message.  This is simply for
        // display purposes as the security model is enforced on the
        // object by the handler.
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render(
                'PelagosAppBundle:MdApp:access-denied.html.twig'
            );
        }

        $objNeeded = array(
            'udi',
            'issueTrackingTicket',
            'datasetSubmission.creationTimeStamp',
            'metadata.id',
            'datasetSubmission.metadataFileName');

        $entityHandler = $this->get('pelagos.entity.handler');
        return $this->render(
            'PelagosAppBundle:MdApp:main.html.twig',
            array(
                'issueTrackingBaseUrl' => $this->getParameter('issue_tracking_base_url'),
                'm_dataset' => array(
                    'submitted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SUBMITTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'inreview' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_IN_REVIEW),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'secondcheck' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SECOND_CHECK),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'accepted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'backtosubmitter' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                ),
            )
        );
    }

    /**
     * Get logfile entries for particular dataset UDI.
     *
     * @param string $udi The dataset UDI identifier.
     *
     * @Route("/getlog/{udi}")
     *
     * @return response
     */
    public function getlog($udi)
    {

        $rawlog = file($this->getParameter('mdapp_logfile'));
        $entries = array_values(preg_grep("/$udi/i", $rawlog));
        $data = null;
        if (count($entries) > 0) {
            $data .= '<ul>';
            foreach ($entries as $entry) {
                $data .= '<li>' . strip_tags($entry) . "</li>\n";
            }
            $data .= '</ul>';
        }
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'text/html');
        return $response;
    }

    /**
     * Create new Metadata from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Route("/upload-metadata-file")
     * @Method("POST")
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person in the Location header.
     */
    public function uploadMetadataFileAction(Request $request)
    {

        $form = $this->get('form.factory')->createNamedBuilder('', FormType::class, null, array('allow_extra_fields' => true))
            ->add('validateSchema', CheckboxType::class)
            ->add('acceptMetadata', CheckboxType::class)
            ->add('overrideDatestamp', CheckboxType::class)
            ->add('test1', CheckboxType::class)
            ->add('test2', CheckboxType::class)
            ->add('test3', CheckboxType::class)
            ->add('newMetadataFile', FileType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $data['newMetadataFile'];
            $originalFileName = $file->getClientOriginalName();

            $errors = array();
            $warnings = array();

            if (1 !== preg_match('/[A-Z]\d\.x\d{3}\.\d{3}-\d{4}/i', $originalFileName, $matches)) {
                //throw new \Exception('UDI not detected in filename!');
                $errors[] = 'UDI not detected in filename!';
            }

            $udi = preg_replace('/-/', ':', $matches[0]);
            $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

            if (0 == count($datasets)) {
                $errors[] = "Dataset with udi:$udi not found!";
                //throw new \Exception("Dataset with udi:$udi not found!");
            } elseif (1 > count($datasets)) {
                throw new \Exception("More than one dataset found with udi:$udi!");
            } else {
                $dataset = $datasets[0];
            }

            try {
                $xml = simplexml_load_file($file->getPathname());
            } catch (\Exception $e) {
                $errors[] = 'Not a parsable XML file!';
            }

            // Seems OK to validate.
            if (0 === count($errors)) {
                if ($data['validateSchema'] == true) {
                    //TODO: Validate XML, you can use $xml.
                    $metadataUtil = $this->get('pelagos.util.metadata');
                    $analysis = $metadataUtil->validateIso($xml->asXML());
                    $errors = array_merge($errors, $analysis['errors']);
                    $warnings = array_merge($warnings, $analysis['warnings']);
                }

                // If there is a geometry, figure out envelope.
                $metadata = new Metadata($dataset, $xml->asXML());
                if (null !== $metadata->getGeometry()) {
                    $geoUtil=$this->get('pelagos.util.geometry');
                    $gml = $metadata->extractBoundingPolygonGML($metadata->getXml())[0];
                    $envelopeWkt = $geoUtil->calculateEnvelopeFromGml($gml);
                }

                if ($data['test1'] == true) {
                    $errorArray = 'errors';
                } else {
                    $errorArray = 'warnings';
                }

                $fileIdentifier = $xml->xpath(
                    '/gmi:MI_Metadata' .
                    '/gmd:fileIdentifier' .
                    '/gco:CharacterString' .
                    '/text()'
                );

                if (count($fileIdentifier) > 0) {
                    if (!(bool) preg_match("/$originalFileName/i", $fileIdentifier[0], $matches)) {
                        ${$errorArray}[] = 'Filename does not match gmd:fileIdentifier!';
                    }
                } else {
                    $warnings[] = 'File Identifier does not exist';
                }

                if ($data['test2'] == true) {
                    $errorArray = 'errors';
                } else {
                    $errorArray = 'warnings';
                }

               $metadataUrl = $xml->xpath(
                    '/gmi:MI_Metadata' .
                    '/gmd:dataSetURI' .
                    '/gco:CharacterString' .
                    '/text()'
                );

                if (count($metadataUrl) > 0) {
                    $udi = $dataset->getUdi();
                    if (false === (bool) preg_match("/\/$udi$/", $metadataUrl[0])) {
                        ${$errorArray}[] = 'UDI does not match metadata URL';
                    }
                } else {
                    $warnings[] = 'Metadata URL does not exist';
                }

                if ($data['test3'] == true) {
                    $errorArray = 'errors';
                } else {
                    $errorArray = 'warnings';
                }

               $distributionUrl = $xml->xpath(
                    '/gmi:MI_Metadata' .
                    '/gmd:distributionInfo' .
                    '/gmd:MD_Distribution' .
                    '/gmd:distributor' .
                    '/gmd:MD_Distributor' .
                    '/gmd:distributorTransferOptions' .
                    '/gmd:MD_DigitalTransferOptions' .
                    '/gmd:onLine' .
                    '/gmd:CI_OnlineResource' .
                    '/gmd:linkage' .
                    '/gmd:URL' .
                    '/text()'
                );

                if (count($distributionUrl) > 0) {
                    $udi = $dataset->getUdi();
                    if (false === (bool) preg_match("/\/$udi$/", $distributionUrl[0])) {
                        ${$errorArray}[] = 'UDI does not match distribution URL.';
                    }
                } else {
                    $warnings[] = 'Distribution URL does not exist';
                }

                if ($data['overrideDatestamp'] == true) {
                    $metadata->updateXmlTimeStamp();
                }
            }
        }
        return $this->render(
            'PelagosAppBundle:MdApp:upload-complete.html.twig',
            array(
                'errors' => $errors,
                'warnings' => $warnings,
                'envelope_wkt' => $envelopeWkt,
                'dataset' => $dataset,
                'orig_filename' => $originalFileName,
                'geoflag' => $dataset->getMetadata()->getGeometry(),
            )
        );

    }
}
