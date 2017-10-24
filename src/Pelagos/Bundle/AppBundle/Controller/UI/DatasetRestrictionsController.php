<?php


namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Elastica\Request;
use Pelagos\Bundle\AppBundle\Controller\Api\EntityController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * The Dataset Restrictions Modifier controller.
 *
 * @Route("/dataset-restrictions")
 */
class DatasetRestrictionsController extends EntityController
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
        $GLOBALS['pelagos']['title'] = 'Dataset Restrictions Modifier';
        return $this->render('PelagosAppBundle:List:DatasetRestrictions.html.twig');
    }


    /**
     *
     * @Route("/{id}")
     *
     * @Method("POST")
     *
     * @param Request       $request     Symfony Request object
     * @param string|null   $id          Dataset Submission ID
     * @return int                       HTTP Response status code
     */
    public function postAction($id, Request $request)
    {
        $entityHandler = $this->container->get('pelagos.entity.handler');
        $datasetSubmission = $this->handleUpdate(DatasetSubmissionType::class, DatasetSubmission::class, $id, $request, 'POST');
        foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
            $entityHandler->update($datasetContact);
        }

        return http_response_code(204);
    }
}
