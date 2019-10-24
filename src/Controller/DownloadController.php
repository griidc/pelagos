<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Bundle\AppBundle\Twig\Extensions as TwigExtentions;

/**
 * The Dataset download controller.
 *
 * @Route("/download")
 */
class DownloadController extends Controller
{
    /**
     * Produce json response for download dialog box.
     *
     * @param string $id The id of the dataset to download.
     *
     * @Route("/{id}")
     *
     * @return Response
     */
    public function defaultAction(string $id)
    {
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
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
     * @param string $id The id of the dataset to download.
     *
     * @Route("/{id}/http")
     *
     * @return Response
     */
    public function httpAction($id)
    {
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
        $downloadFileInfo = $this->get('pelagos.util.data_store')->getDownloadFileInfo($dataset->getUdi(), 'dataset');
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
            if ($type == 'Pelagos\Entity\Account') {
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

        $this->container->get('pelagos.event.log_action_item_event_dispatcher')->dispatch(
            array(
                'actionName' => 'File Download',
                'subjectEntityName' => $em->getClassMetadata(get_class($dataset))->getName(),
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
