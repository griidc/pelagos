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
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Twig\Extensions as TwigExtentions;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * Download dataset, count and forward to zip stream.
     *
     * @Route("/download/dataset/{id}", name="pelagos_app_download_dataset")
     */
    public function downloadCount(Dataset $dataset, LogActionItemEventDispatcher $logActionItemEventDispatcher): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser instanceof Account) {
            $type = 'GoMRI';
            $typeId = $currentUser->getUserId();
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

        $downloadZip = $this->generateUrl('pelagos_api_download_zip', [
            'dataset' => $dataset->getId(),
        ]);

        return new RedirectResponse($downloadZip);
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
        if ($dataset->isRestricted()) {
            throw new BadRequestHttpException('Unable to download restricted dataset');
        }
        if ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_ACCEPTED) {
            throw new BadRequestHttpException('Unable to download non-accepted dataset');
        }
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileset = $datasetSubmission->getFileset();

            if ($fileset instanceof Fileset) {
                if (!$fileset->doesZipFileExist()) {
                    $filePhysicalPath = $fileset->getProcessedFiles()->first()->getPhysicalFilePath();
                    try {
                        $fileStream = $dataStore->getFile($filePhysicalPath);
                    } catch (\Exception $exception) {
                        throw new BadRequestHttpException('Unable to open file');
                    }
                    $response = new StreamedResponse();
                    $response->setCallback(function () use ($fileStream) {
                        $outputStream = GuzzlePsr7Utils::streamFor(fopen('php://output', 'wb'));
                        GuzzlePsr7Utils::copyToStream($fileStream, $outputStream);
                    });
                    $filename = $datasetSubmission->getDatasetFileName();
                    $mimeType = $dataStore->getMimeType($filePhysicalPath) ?: 'application/octet-stream';
                } else {
                    $zipFilePath = $fileset->getZipFilePath();
                    $response = new StreamedResponse(function () use ($zipFilePath) {
                        $outputStream = fopen('php://output', 'wb');
                        $fileStream = fopen($zipFilePath, 'r');
                        stream_copy_to_stream($fileStream, $outputStream);
                    });
                    $filename = basename($zipFilePath);
                    $mimeType = 'application/zip';
                }

                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $filename
                );

                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-type', $mimeType);

                if ($this->getUser()) {
                    $type = get_class($this->getUser());
                    if ($type == 'App\Entity\Account') {
                        $type = 'GoMRI';
                        $typeId = $this->getUser()->getUserId();
                    } else {
                        $type = 'Non-GoMRI';
                        $typeId = $this->getUser()->getUserIdentifier();
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
                return $response;
            }
        } else {
            throw new BadRequestHttpException('No files found in this dataset');
        }
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
            'availability' => $dataset->getAvailabilityStatus(),
        );

        if ($datasetSubmission instanceof DatasetSubmission) {
            $datasetInfo['filename'] = str_replace(':', '.', $dataset->getUdi()) . '.zip';
            $datasetInfo['fileSize'] = TwigExtentions::formatBytes($datasetSubmission->getDatasetFileSize(), 2);
            $datasetInfo['fileSizeRaw'] = $datasetSubmission->getDatasetFileSize();
            $datasetInfo['checksum'] = $datasetSubmission->getDatasetFileSha256Hash();
            $datasetInfo['coldStorage'] = $datasetSubmission->isDatasetFileInColdStorage();
        }
        return $datasetInfo;
    }
}
