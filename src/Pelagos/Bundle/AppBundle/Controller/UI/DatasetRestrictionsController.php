<?php


namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * The Dataset Restrictions Modifier controller.
 *
 * @Route("/dataset-restrictions", name="dataset_restriction")
 */
class DatasetRestrictionsController extends UIController
{
    /**
     * Dataset Restrictions Modifier UI.
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

       // TODO Need to create a new twig for the dataset restrictions and return that twig //
        return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
    }
}