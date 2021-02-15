<?php

namespace App\Controller;

use App\Entity\Fileset;
use App\Event\LogActionItemEventDispatcher;
use App\Handler\EntityHandler;
use App\Util\Datastore;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;

use App\Twig\Extensions as TwigExtentions;

/**
 * The Dataset download controller.
 */
class DownloadController extends AbstractController
{
    /**
     * Entity Handler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * The download base directory path.
     *
     * @var string
     */
    protected $downloadBaseDir;

    /**
     * The download base url.
     *
     * @var string
     */
    protected $downloadBaseUrl;

    /**
     * DownloadController constructor.
     *
     * @param EntityHandler $entityHandler   The entity handler.
     * @param string        $downloadBaseDir The download base directory path.
     * @param string        $downloadBaseUrl The download base url.
     */
    public function __construct(EntityHandler $entityHandler, string $downloadBaseDir, string $downloadBaseUrl)
    {
        $this->entityHandler = $entityHandler;
        $this->downloadBaseDir = $downloadBaseDir;
        $this->downloadBaseUrl = $downloadBaseUrl;
    }

    /**
     * Produce json response for download dialog box.
     *
     * @param integer $id The id of the dataset to download.
     *
     * @Route("/download/{id}", name="pelagos_app_download_default")
     *
     * @return Response
     */
    public function defaultAction(int $id)
    {
        $dataset = $this->entityHandler->get(Dataset::class, $id);
        if ($dataset->isRemotelyHosted()) {
            $result = array(
                'dataset' => $this->getDatasetDetails($dataset),
                'remotelyHosted' => true,
                'fileUri' => $dataset->getDatasetSubmission()->getRemotelyHostedUrl()
            );
        } else {
            $result = array(
                'dataset' => $this->getDatasetDetails($dataset),
                'remotelyHosted' => false,
                'guest' => !$this->getUser() instanceof Account,
                'gridOK' => $this->getUser() instanceof Account and $this->getUser()->isPosix()
            );
        }
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Set up direct download via HTTP and produce html for direct download splash screen.
     *
     * @param Dataset                      $dataset                      The id of the dataset to download.
     * @param Datastore                    $dataStore                    The data store.
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher The log action dispatcher.
     *
     * @Route("/download/{id}/http", name="pelagos_app_download_http")
     *
     * @return Response
     */
    public function httpAction(Dataset $dataset, Datastore $dataStore, LogActionItemEventDispatcher $logActionItemEventDispatcher)
    {
        $datasetSubmission = $dataset->getDatasetSubmission();
        $response = new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileset = $datasetSubmission->getFileset();

            if ($fileset instanceof Fileset) {
                if (!$fileset->doesZipFileExist()) {
                    $filePhysicalPath = $fileset->getProcessedFiles()->first()->getPhysicalFilePath();
                    $response = new StreamedResponse(function () use ($dataStore, $filePhysicalPath) {
                        $outputStream = fopen('php://output', 'wb');
                        $fileStream = $dataStore->getFile($filePhysicalPath);
                        if (is_array($fileStream)) {
                            stream_copy_to_stream($fileStream['fileStream'], $outputStream);
                        } else {
                            throw new BadRequestHttpException('Unable to open file');
                        }
                    });

                    $disposition = HeaderUtils::makeDisposition(
                        HeaderUtils::DISPOSITION_ATTACHMENT,
                        $datasetSubmission->getDatasetFileName()
                    );
                    $response->headers->set('Content-Disposition', $disposition);
                } else {
                    $response = $this->forward('App\Controller\Api\FileManager::downloadZipAllFiles', [$datasetSubmission->getId()]);
                }
            }

            if ($this->getUser()) {
                $type = get_class($this->getUser());
                if ($type == 'App\Entity\Account') {
                    $type = 'GoMRI';
                    $typeId = $this->getUser()->getUserId();
                } else {
                    $type = 'Non-GoMRI';
                    $typeId = $this->getUser()->getUsername();
                }
            } else {
                $type = 'Non-GoMRI';
                $typeId = 'anonymous';
            }

            $logActionItemEventDispatcher->dispatch(
                array(
                    'actionName' => 'File Download',
                    'subjectEntityName' => 'Pelagos\Entity\Dataset',
                    'subjectEntityId' => $dataset->getId(),
                    'payLoad' => array('userType' => $type, 'userId' => $typeId),
                ),
                'file_download'
            );
        }

        return $response;
    }

    /**
     * Get the dataset required information for the Download dialog box.
     *
     * @param Dataset $dataset A dataset instance.
     *
     * @return array
     */
    private function getDatasetDetails(Dataset $dataset): array
    {
        $datasetSubmission = $dataset->getDatasetSubmission();
        $datasetInfo = array(
            'udi' => $dataset->getUdi(),
            'availability' => $dataset->getAvailabilityStatus()
        );

        if ($datasetSubmission instanceof DatasetSubmission) {
            $datasetInfo['filename'] = $datasetSubmission->getDatasetFileName();
            $datasetInfo['fileSize'] = TwigExtentions::formatBytes($datasetSubmission->getDatasetFileSize(), 2);
            $datasetInfo['fileSizeRaw'] = $datasetSubmission->getDatasetFileSize();
            $datasetInfo['checksum'] = $datasetSubmission->getDatasetFileSha256Hash();
        }
        return $datasetInfo;
    }
}
