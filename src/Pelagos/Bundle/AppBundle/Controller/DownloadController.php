<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\Dataset;

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
        return new Response();
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
