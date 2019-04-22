<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\ExternalDownloadLogType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The end review tool helps to end the review of a dataset submission review.
 *
 * @Route("/external-download-log")
 */
class ExternalDownloadLogController extends UIController
{

    /**
     * The default action for End Review.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $form = $this->get('form.factory')->createNamed(
            'externalDownloadLog',
            ExternalDownloadLogType::class
        );
        return $this->render('PelagosAppBundle:ExternalDownloadLog:default.html.twig', array('form' => $form->createView()));
    }
}
