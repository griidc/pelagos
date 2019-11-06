<?php
namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Handler\EntityHandler;
use App\Event\LogActionItemEventDispatcher;
use App\Exception\PersistenceException;

use App\Util\RabbitPublisher;
use Doctrine\ORM\EntityManagerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The Dataset Restrictions Modifier controller.
 */
class DatasetRestrictionsController extends AbstractController
{
    /**
     * Class variable for dependency injection, an event dispatcher.
     *
     * @var LogActionItemEventDispatcher $logActionItemEventDispatcher
     */
    protected $logActionItemEventDispatcher;

    /**
     * Class variable for dependency injection - entityManager.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Custom rabbitmq publisher.
     *
     * @var RabbitPublisher
     */
    protected $publisher;

    /**
     * Class constructor for Dependency Injections.
     *
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher A Pelagos action dispatcher.
     * @param EntityManagerInterface       $entityManager                A Doctrine ORM entity manager.
     * @param RabbitPublisher              $publisher                    Custom rabbitmq publisher.
     */
    public function __construct(
        LogActionItemEventDispatcher $logActionItemEventDispatcher,
        EntityManagerInterface $entityManager,
        RabbitPublisher $publisher
    ) {
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
        $this->entityManager = $entityManager;
        $this->publisher = $publisher;
    }

    /**
     * Dataset Restrictions Modifier UI.
     *
     * @Route(
     *      "/dataset-restrictions",
     *      name="pelagos_app_ui_datasetrestrictions_default",
     *      methods={"GET"}
     * )
     *
     * @return Response
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $GLOBALS['pelagos']['title'] = 'Dataset Restrictions Modifier';
        return $this->render('List/DatasetRestrictions.html.twig');
    }

    /**
     * Update restrictions for the dataset.
     *
     * This updates the dataset submission restrictions property.Dataset Submission PATCH API exists,
     * but doesn't work with Symfony.
     *
     * @param Request       $request       The HTTP request.
     * @param string        $id            The entity ID of a Dataset.
     * @param EntityHandler $entityHandler A Pelagos entity handler.
     *
     * @Route(
     *      "/dataset-restrictions/{id}",
     *      name="pelagos_app_ui_datasetrestrictions_post",
     *      methods={"POST"}
     * )
     *
     * @throws PersistenceException    Exception thrown when update fails.
     * @throws BadRequestHttpException Exception thrown when restriction key is null.
     *
     * @return Response
     */
    public function postAction(Request $request, string $id, EntityHandler $entityHandler)
    {
        $restrictionKey = $request->request->get('restrictions');

        $datasets = $entityHandler->getBy(Dataset::class, array('id' => $id));

        // RabbitMQ message to update the DOI for the dataset.
        $rabbitMessage = array(
            'body' => $id,
            'routing_key' => 'update'
        );

        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetSubmission = $dataset->getDatasetSubmission();
            $datasetStatus = $dataset->getDatasetStatus();

            if ($restrictionKey) {
                // Record the original state for logging purposes before changing it.
                $from = $datasetSubmission->getRestrictions();
                $actor = $this->get('security.token_storage')->getToken()->getUser()->getUserId();
                $this->dispatchLogRestrictionsEvent($dataset, $actor, $from, $restrictionKey);

                $datasetSubmission->setRestrictions($restrictionKey);

                try {
                    $entityHandler->update($datasetSubmission);
                } catch (PersistenceException $exception) {
                    throw new PersistenceException($exception->getMessage());
                }

                if ($datasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
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

        $this->publisher->publish(
            $rabbitMessage['body'],
            RabbitPublisher::DOI_PRODUCER,
            $rabbitMessage['routing_key']
        );
    }

    /**
     * Log restriction changes.
     *
     * @param Dataset $dataset          The dataset having restrictions modified.
     * @param string  $actor            The username of the person modifying the restriction.
     * @param string  $restrictionsFrom The original restriction.
     * @param mixed   $restrictionsTo   The restriction that was put in place.
     *
     * @return void
     */
    protected function dispatchLogRestrictionsEvent(Dataset $dataset, string $actor, string $restrictionsFrom, $restrictionsTo)
    {
        $this->logActionItemEventDispatcher->dispatch(
            array(
                'actionName' => 'Restriction Change',
                'subjectEntityName' => $this->entityManager->getClassMetadata(get_class($dataset))->getName(),
                'subjectEntityId' => $dataset->getId(),
                'payLoad' => array(
                    'userId' => $actor,
                    'previousRestriction' => $restrictionsFrom,
                    'newRestriction' => $restrictionsTo,
                )
            ),
            'restrictions_log'
        );
    }
}
