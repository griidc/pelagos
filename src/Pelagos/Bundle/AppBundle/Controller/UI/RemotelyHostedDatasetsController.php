<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Response\TerminateResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Remotely Hosted Datasets list controller.
 *
 * @Route("/remotelyhosted-datasets")
 */
class RemotelyHostedDatasetsController extends UIController
{
    /**
     * Default action of Remotely Hosted Datasets.
     *
     * @Route("")
     *
     * @return Response A response instance.
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

    /**
     * Mark as Remotely Hosted Dataset.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/{udi}")
     *
     * @Method("POST")
     *
     * @return TerminateResponse A response.
     */
    public function postAction(Request $request)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        
        return new TerminateResponse('', 204);
    }
}
