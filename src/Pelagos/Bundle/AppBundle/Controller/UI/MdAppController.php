<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Pelagos\Bundle\AppBundle\Form\MdappType;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Doctrine\ORM\Query;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

use Pelagos\Exception\InvalidGmlException;

/**
 * The MDApp controller.
 *
 * @Route("/mdapp")
 */
class MdAppController extends UIController implements OptionalReadOnlyInterface
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
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

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

        $metadataXml = $this->get('pelagos.util.metadata')->getXmlRepresentation($dataset);

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
        $message = null;
        if (null !== $to) {
            if ('Accepted' == $to) {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $datasetSubmission->setMetadataStatus($to);
                $entityHandler->update($datasetSubmission);
                $entityHandler->update($dataset);
                $mdappLogger->writeLog($this->getUser()->getUsername() .
                    " has changed metadata status for $udi ($from -> $to) (mdapp msg)");
                $message = "Status for $udi has been changed from $from to $to.";
                $this->container->get('pelagos.event.entity_event_dispatcher')->dispatch(
                    $datasetSubmission,
                    'approved'
                );
            } else {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $datasetSubmission->setMetadataStatus($to);
                $entityHandler->update($datasetSubmission);
                $entityHandler->update($dataset);
                $mdappLogger->writeLog($this->getUser()->getUsername() .
                    " has changed metadata status for $udi ($from -> $to) (mdapp msg)");
                $message = "Status for $udi has been changed from $from to $to.";
            }
        }

        $this->get('session')->getFlashBag()->add('notice', $message);
        return $this->redirectToRoute('pelagos_app_ui_mdapp_default');
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
            'datasetSubmission.submissionTimeStamp',
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
     * @throws \Exception If more than one dataset found by UDI.
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person in the Location header.
     */
    public function uploadMetadataFileAction(Request $request)
    {
        $form = $this
            ->get('form.factory')->createNamedBuilder(
                '',
                FormType::class,
                null,
                array('allow_extra_fields' => true)
            )
            ->add('validateSchema', CheckboxType::class)
            ->add('acceptMetadata', CheckboxType::class)
            ->add('overrideDatestamp', CheckboxType::class)
            ->add('test1', CheckboxType::class)
            ->add('test2', CheckboxType::class)
            ->add('test3', CheckboxType::class)
            ->add('newMetadataFile', FileType::class)
            ->getForm();

        $form->handleRequest($request);

        $twigArray = array();

        if ($form->isSubmitted() && $form->isValid()) {
            $twigArray = $this->processForm($form);
        }

        return $this->render(
            'PelagosAppBundle:MdApp:upload-complete.html.twig',
            $twigArray
        );

    }

    /**
     * Check if an UDI is in the File Name.
     *
     * @param string $filename The File Name.
     *
     * @return boolean
     */
    private function isAnUdiInFilename($filename)
    {
        $hasUdi = false;
        if (1 === preg_match('/[A-Z\d]{2}\.x\d{3}\.\d{3}-\d{4}/i', $filename)) {
            $hasUdi = true;
        }
        return $hasUdi;
    }

    /**
     * Check the see if file name pattern has an UDI in it.
     *
     * @param string $filename The File Name.
     *
     * @return boolean
     */
    private function checkFilenameFormat($filename)
    {
        if (1 === preg_match('/^[A-Z\d]{2}\.x\d{3}\.\d{3}-\d{4}-metadata.xml/', $filename)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the UDI from uploaded Metdata file name.
     *
     * @param string $filename The File Name.
     *
     * @return string The UDI.
     */
    private function getUdiFromFilename($filename)
    {
        $matches = array();
        $hasUdi = preg_match('/^.*([A-Z\d]{2}\.x\d{3}\.\d{3}-\d{4}).*$/', $filename, $matches);
        if ($hasUdi === 1) {
            return preg_replace('/-/', ':', $matches[1]);
        } else {
            return null;
        }
    }

    /**
     * Retrieves the Dataset from an UDI.
     *
     * @param string $udi The UDI.
     *
     * @throws \Exception When more than one datasets were found.
     *
     * @return Dataset The found Dataset or null, if not found.
     */
    private function getDataset($udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));
        if (0 == count($datasets)) {
            $dataset = null;
        } elseif (1 > count($datasets)) {
            throw new \Exception("More than one dataset found with udi:$udi!");
        } else {
            $dataset = $datasets[0];
        }
        return $dataset;
    }

    /**
     * Processes the submitted form.
     *
     * @param Form $form A Symfony Form to be processed.
     *
     * @return array A twig array for the output.
     */
    private function processForm(Form $form)
    {
        $mdappLogger = $this->get('pelagos.util.mdapplogger');

        $errors = array();
        $warnings = array();
        $udi = null;
        $boundingBoxArray = array();
        $gml = null;
        $geometry = null;
        $envelopeWkt = null;
        $boundingBoxArray = null;
        $okToValidate = true;

        $data = $form->getData();
        $file = $data['newMetadataFile'];

        if (null === $file) {
            $errors[] = 'No file was selected for upload.';
            $errStr = implode(', ', $errors);
            $mdappLogger->writeLog('Failed upload attempt on: '
                . ' unknown UDI '
                . ' by: '
                . $this->getUser()->getUsername()
                . ' Reason: '
                . $errStr
                . ' (mdapp msg)');

            return array(
                'errors' => $errors,
                'warnings' => null,
                'dataset' => null,
                'orig_filename' => null,
                'geometryWkt' => null,
                'envelopeWkt' => null,
                'message' => null,
                'udi' => null,
                'isoValid' => null,
            );
        }

        $originalFileName = $file->getClientOriginalName();

        // Check to see if filename is in correct format.
        if ($this->checkFilenameFormat($originalFileName)) {
        } else {
            $errors[] = "Bad filename $originalFileName. Filename must be in the form of UDI-metadata.xml.";
        }

        // Attempt to get UDI from filename.
        if ($this->isAnUdiInFilename($originalFileName)) {
            $udi = $this->getUdiFromFilename($originalFileName);
        } else {
            $errors[] = 'UDI not detected in filename!';
        }

        // Attempt to query model for Dataset.
        $dataset = $this->getDataset($udi);
        if (null === $dataset) {
            $errors[] = "Dataset with udi:$udi not found!";
        } else {
            // Check to see if the dataset has a completed submission.
            // (If a submission is attached to a dataset, it has to be complete.)
            $datasetSubmission = $dataset->getDatasetSubmission();
            if (false === $datasetSubmission instanceof DatasetSubmission) {
                $errors[] = 'You may not upload XML for a dataset lacking a completed submission.';
            }
        }

        // Attempt to parse uploaded file.
        libxml_use_internal_errors(true);
        $testDoc = new \DomDocument('1.0', 'UTF-8');
        $tmpp = @$testDoc->load($file->getPathname());
        if (!$tmpp) {
            $errors[] = 'Not a parsable XML file!';
            $parsable = false;
        } else {
            $parsable = true;
            $xml = simplexml_load_file($file->getPathname());
        }

        $message = 'Metadata was not uploaded due to errors as described.';

        // Since it parsed, try looking for a geometry.
        if ($parsable) {
            $boundingBoxArray = array();
            $geoUtil = $this->get('pelagos.util.geometry');
            $gmls = Metadata::extractBoundingPolygonGML($xml);
            // If there is a geometry, figure out envelope and bounding box array,
            // otherwise, leave them set to null.
            if (count($gmls) > 0) {
                $gml = $gmls[0];
                try {
                    $geometry = $geoUtil->convertGmlToWkt($gml);
                } catch (InvalidGmlException $e) {
                    $errors[] = $e->getMessage() . ' while attempting GML to WKT conversion';
                    $geometry = null;
                }

                try {
                    $envelopeWkt = $geoUtil->calculateEnvelopeFromGml($gml);
                } catch (InvalidGmlException $e) {
                    $errors[] = $e->getMessage() . ' while attempting to calculate envelope from gml';
                    $envelopeWkt = null;
                }

                try {
                    $boundingBoxArray = $geoUtil->calculateGeographicBoundsFromGml($gml);
                } catch (InvalidGmlException $e) {
                    $errors[] = $e->getMessage() . ' while attempting to calculate bonding box from gml';
                    $boundingBoxArray = array();
                }
            }
        }

        // Seems OK to validate.
        $schemaValidated = false;
        if ($parsable) {
            $this->xmlChecks($xml, $data, $errors, $warnings, $originalFileName, $udi, $schemaValidated);
        }

        if (count($errors) === 0) {
            // Get or create new Metadata.
            if ($dataset->getMetadata() instanceof Metadata) {
                $metadata = $dataset->getMetadata();
                $metadata->setXml($xml->asXML());
            } else {
                $metadata = new Metadata($dataset, $xml->asXML());
                $this->entityHandler->create($metadata);
            }

            if ($data['overrideDatestamp'] == true) {
                $metadata->updateXmlTimeStamp();
            }

            // Only do this if we have identified a geometry in the file.
            if (null !== $geometry) {
                $metadata->addBoundingBoxToXml($boundingBoxArray);
            }

            if (true == $data['acceptMetadata']) {
                $dataset->setMetadataStatus(DatasetSubmission::METADATA_STATUS_ACCEPTED);
            }

            $this->entityHandler->update($metadata);
            $this->entityHandler->update($dataset);
            $message = 'Metadata has been successfully uploaded.';

            $loginfo = $this->getUser()->getUsername() . ' successfully uploaded metadata for ' . $udi;
            if (true == $data['acceptMetadata']) {
                $loginfo .= ' and data was flagged as accepted';
            }
            $loginfo .= '. (mdapp msg)';
            $mdappLogger->writeLog($loginfo);
        } else {
            // Log why the upload failed.
            $errStr = implode(', ', $errors);
            $mdappLogger->writeLog('Failed upload attempt on: '
                . $udi
                . ' by: '
                . $this->getUser()->getUsername()
                . ' Reason: '
                . $errStr
                . ' (mdapp msg)');
        }

        return array(
            'errors' => $errors,
            'warnings' => $warnings,
            'dataset' => $dataset,
            'orig_filename' => $originalFileName,
            'geometryWkt' => $geometry,
            'envelopeWkt' => $envelopeWkt,
            'message' => $message,
            'udi' => $udi,
            'isoValid' => $schemaValidated,
        );
    }

    /**
     * Runs some common xml checks.
     *
     * @param string  $xml              The XML to be checked.
     * @param array   $data             Form data array.
     * @param array   $errors           Errors array.
     * @param array   $warnings         Warning array.
     * @param string  $originalFileName The original filename.
     * @param string  $udi              The UDI that goes with XML filename.
     * @param boolean $schemaValidated  Flag whether XML is schema validated.
     *
     * @return void
     */
    private function xmlChecks(
        $xml,
        array &$data,
        array &$errors,
        array &$warnings,
        &$originalFileName,
        &$udi,
        &$schemaValidated
    ) {
        if ($data['validateSchema'] == true) {
            // put schema errors into error array as hard errors
            $metadataUtil = $this->get('pelagos.util.metadata');
            $analysis = $metadataUtil->validateIso($xml->asXML());
            $errors = array_merge($errors, $analysis['errors']);
            $warnings = array_merge($warnings, $analysis['warnings']);
            if (count($analysis['errors']) === 0) {
                $schemaValidated = true;
            }
        } else {
            // just warn of schema errors by putting into warning array
            $metadataUtil = $this->get('pelagos.util.metadata');
            $analysis = $metadataUtil->validateIso($xml->asXML());
            $warnings = array_merge($warnings, $analysis['errors']);
            $warnings = array_merge($warnings, $analysis['warnings']);
            if (count($analysis['errors']) === 0) {
                $schemaValidated = true;
            }
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
            if (!(bool) preg_match("/^$originalFileName$/i", $fileIdentifier[0], $matches)) {
                ${$errorArray}[] = 'Filename does not match gmd:fileIdentifier!';
            }
        } else {
            ${$errorArray}[] = 'File Identifier is missing or blank.';
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
            if (false === (bool) preg_match("/\/$udi$/", $metadataUrl[0])) {
                ${$errorArray}[] = 'UDI does not match metadata URL';
            }
        } else {
            ${$errorArray}[] = 'Metadata URL is missing or blank.';
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
            if (false === (bool) preg_match("/\/$udi$/", $distributionUrl[0])) {
                ${$errorArray}[] = 'UDI does not match distribution URL.';
            }
        } else {
            ${$errorArray}[] = 'Distribution URL is missing or blank.';
        }
    }
}
