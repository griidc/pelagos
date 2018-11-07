<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * The Dataset download controller.
 *
 * @Route("/download")
 */
class DownloadController extends Controller
{
    /**
     * Produce html for download splash screen.
     *
     * @param Request $request The Symfony request object.
     * @param string  $id      The id of the dataset to download.
     *
     * @Route("/{id}")
     *
     * @return Response
     */
    public function defaultAction(Request $request, $id)
    {
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission
            and DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED ===
                $dataset->getDatasetSubmission()->getDatasetFileTransferStatus()) {
            return $this->render(
                'PelagosAppBundle:Download:download-external-resource-splash-screen.html.twig',
                array(
                    'dataset' => $dataset,
                )
            );
        }
        return $this->render(
            'PelagosAppBundle:Download:download-splash-screen.html.twig',
            array(
                'dataset' => $dataset,
                'guest' => !$this->getUser() instanceof Account,
                'gridOK' => $this->getUser() instanceof Account and $this->getUser()->isPosix(),
            )
        );
    }

    /**
     * Set up direct download via HTTP and produce html for direct download splash screen.
     *
     * @param string $id The id of the dataset to download.
     *
     * @throws AccessDeniedException When no user is authenticated.
     *
     * @Route("/{id}/http")
     *
     * @return Response
     */
    public function httpAction($id)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException('You must log in to download datasets');
        }
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
        $downloadFileInfo = $this->get('pelagos.util.data_store')->getDownloadFileInfo($dataset->getUdi(), 'dataset');
        $uniqueDirectory = uniqid(
            preg_replace('/\s/', '_', $this->getUser()->getUsername()) . '_'
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
        $type = get_class($this->getUser());
        if ($type == 'Pelagos\Entity\Account') {
            $type = 'GoMRI';
            $typeId = $this->getUser()->getUserId();
        } else {
            $type = 'Non-GoMRI';
            $typeId = $this->getUser()->getUsername();
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
        return $this->render(
            'PelagosAppBundle:Download:download-via-http-splash-screen.html.twig',
            array(
                'dataset' => $dataset,
                'downloadUrl' => $downloadBaseUrl . '/' . $uniqueDirectory . '/' . $datasetFileName,
            )
        );
    }

    /**
     * Set up download via GridFTP and produce html for GridFTP download splash screen.
     *
     * @param string $id The id of the dataset to download.
     *
     * @throws AccessDeniedException When no user is authenticated.
     * @throws AccessDeniedException When a guest user attempts to download via GridFTP.
     *
     * @Route("/{id}/grid-ftp")
     *
     * @return Response
     */
    public function gridFtpAction($id)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException('You must log in to download datasets');
        }
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
        if (!$this->getUser() instanceof Account) {
            throw $this->createAccessDeniedException('Only GRIIDC users can use GridFTP');
        }
        $downloadFileInfo = $this->get('pelagos.util.data_store')->getDownloadFileInfo($dataset->getUdi(), 'dataset');
        $homeDirectory = $this->getUser()->getHomeDirectory();
        $downloadDirectory = $homeDirectory . '/download';
        $datasetDownloadDirectory = $downloadDirectory . '/' . $dataset->getUdi();
        if (!file_exists($datasetDownloadDirectory)) {
            mkdir($datasetDownloadDirectory, 0755);
        }
        $linkFile = $datasetDownloadDirectory . '/' . $dataset->getDatasetSubmission()->getDatasetFileName();
        if (file_exists($linkFile)) {
            unlink($linkFile);
        }
        symlink($downloadFileInfo->getRealPath(), $linkFile);
        return $this->render(
            'PelagosAppBundle:Download:download-via-gridftp-splash-screen.html.twig',
            array(
                'dataset' => $dataset,
            )
        );
    }
}
