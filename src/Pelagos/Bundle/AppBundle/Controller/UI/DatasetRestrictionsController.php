<?php


namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Exception\PersistenceException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The Dataset Restrictions Modifier controller.
 *
 * @Route("/dataset-restrictions")
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
     * @throws BadRequestHttpException Exception thrown when restriction key is null.
     *
     * @return Response
     */
    public function postAction(Request $request, $id)
    {
        $restrictionKey = $request->request->get('restrictions');

        $datasets = $this->entityHandler->getBy(Dataset::class, array('id' => $id));

        // RabbitMQ message to update the DOI for the dataset.
        $rabbitMessage = array(
            'body' => $id,
            'routing_key' => 'publish'
        );

        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetSubmission = $dataset->getDatasetSubmission();
            $datasetStatus = $dataset->getMetadataStatus();

            if ($restrictionKey) {
                $datasetSubmission->setRestrictions($restrictionKey);

                try {
                    $this->entityHandler->update($datasetSubmission);
                } catch (PersistenceException $exception) {
                    throw new PersistenceException($exception->getMessage());
                }

                if ($datasetStatus === DatasetSubmission::METADATA_STATUS_ACCEPTED) {
                    $this->publishDoiForAccepted($rabbitMessage);
                }

            } else {
                // Send 500 response code if restriction key is null
                throw new BadRequestHttpException('Restiction key is null');
            }
        }
        // Send 204(okay) if the restriction key is not null and updated is successful

        return new Response('', 204);
    }

    /**
     * Method to publish doi for accepted datasets.
     *
     * @param array $rabbitMessage The rabbitMq message that needs to be published.
     *
     * @return void
     */
    private function publishDoiForAccepted(array $rabbitMessage)
    {
       // Publish the message to DoiConsumer to update the DOI.

        $this->get('old_sound_rabbit_mq.doi_issue_producer')->publish(
            $rabbitMessage['body'],
            $rabbitMessage['routing_key']
        );
    }
}
