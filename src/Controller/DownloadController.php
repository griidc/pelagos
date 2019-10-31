<?php

namespace App\Controller;

use App\Event\LogActionItemEventDispatcher;
use App\Handler\EntityHandler;
use App\Util\DataStore;
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
     * DownloadController constructor.
     *
     * @param EntityHandler $entityHandler The entity handler.
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
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
                'fileUri' => $dataset->getDatasetSubmission()->getDatasetFileUri()
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
     * @param integer                      $id                           The id of the dataset to download.
     * @param DataStore                    $dataStore                    The data store.
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher The log action dispatcher.
     *
     * @Route("/download/{id}/http", name="pelagos_app_download_http")
     *
     * @return Response
     */
    public function httpAction(int $id, DataStore $dataStore, LogActionItemEventDispatcher $logActionItemEventDispatcher)
    {
        $dataset = $this->entityHandler->get(Dataset::class, $id);
        $downloadFileInfo = $dataStore->getDownloadFileInfo($dataset->getUdi(), 'dataset');
        $username = null;
        if ($this->getUser()) {
            $username = $this->getUser()->getUsername();
        } else {
            $username = '';
        }
        $uniqueDirectory = uniqid(
            preg_replace('/\s/', '_', $username) . '_'
        );
        $downloadBaseDirectory = $this->getParameter('download_base_directory');
        $downloadDirectory = $downloadBaseDirectory . '/' . $uniqueDirectory;
        mkdir($downloadDirectory, 0755);
        $datasetFileName  = $dataset->getDatasetSubmission()->getDatasetFileName();
        symlink(
            $downloadFileInfo->getRealPath(),
            $downloadDirectory . '/' . $datasetFileName
        );
        $downloadBaseUrl = $this->getParameter('download_base_url');
        $em = $this->container->get('doctrine')->getManager();

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
        $response = new Response(json_encode(['downloadUrl' => $downloadBaseUrl . '/' . $uniqueDirectory . '/' . $datasetFileName]));
        $response->headers->set('Content-Type', 'application/json');

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
