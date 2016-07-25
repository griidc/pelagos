<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render(
                'PelagosAppBundle:Download:log-in-to-download-splash-screen.html.twig',
                array(
                    'refererPath' => parse_url(
                        $request->headers->get('referer'),
                        PHP_URL_PATH
                    ),
                )
            );
        }
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
                'guest' => false,
                'gridOK' => true,
            )
        );
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
        $uniqueDirectory = uniqid(
            preg_replace('/\s/', '_', $this->getUser()->getUsername()) . '_'
        );
        $downloadBaseDirectory = $this->getParameter('download_base_directory');
        $downloadDirectory = $downloadBaseDirectory . '/' . $uniqueDirectory;
        mkdir($downloadDirectory);
        $datasetFileName  = $dataset->getDatasetSubmission()->getDatasetFileName();
        symlink(
            $downloadFileInfo->getRealPath(),
            $downloadDirectory . '/' . $datasetFileName
        );
        $downloadBaseUrl = $this->getParameter('download_base_url');
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
     * @Route("/{id}/grid-ftp")
     *
     * @return Response
     */
    public function gridFtpAction($id)
    {
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);
        return new Response();
    }
}
