<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * The Remotely Hosted Datasets list controller.
 *
 * @Route("/remotelyhosted-datasets")
 */
class RemotelyHostedDatasetsController extends UIController
{
    /**
     * Remotely Hosted Datasets UI.
     *
     * @Route("")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $GLOBALS['pelagos']['title'] = 'Remotely Hosted Datasets';
        return $this->render('PelagosAppBundle:List:RemotelyHostedDatasets.html.twig');
    }
}
