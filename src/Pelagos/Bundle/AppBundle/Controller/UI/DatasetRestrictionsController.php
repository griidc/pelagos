<?php


namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Controller\Api\EntityController;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Exception\PersistenceException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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

        $GLOBALS['pelagos']['title'] = 'Dataset Restrictions Modifier';
        return $this->render('PelagosAppBundle:List:DatasetRestrictions.html.twig');
    }

    /**
     * Update restrictions for the dataset.
     *
     * This updates the dataset submission restrictions property.Dataset Submission PATCH API exists,
     * but doesn't work with Symfony.
     *
     * @param Request $request HTTP Symfony Request object.
     * @param string  $id      Dataset Submission ID.
     *
     * @Route("/{id}")
     *
     * @Method("POST")
     *
     * @throws PersistenceException Exception thrown when update fails.
     * @return int HTTP Response status code.
     */
    public function postAction(Request $request, $id)
    {
        $entityHandler = $this->container->get('pelagos.entity.handler');
        $entity = $this->handleGetOne(DatasetSubmission::class, $id);
        $restrictionKey = $request->request->get('restrictions');

        if ($restrictionKey) {
            $entity->setRestrictions($restrictionKey);

            try {

                $entityHandler->update($entity);

            } catch (PersistenceException $exception) {
                throw new PersistenceException($exception->getMessage());
            }

            // Send 204(okay) if the restriction key is not null and updated is successful
            return http_response_code(204);
        }
        // Send 500 response code if restriction key is null

        return http_response_code(500);
    }
}
