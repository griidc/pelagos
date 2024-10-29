<?php

namespace App\Controller\UI;

use App\Entity\Account;
use App\Entity\Dataset;
use App\Handler\EntityHandler;
use App\Event\LogActionItemEventDispatcher;
use App\Exception\PersistenceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * Class constructor for Dependency Injections.
     *
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher A Pelagos action dispatcher.
     * @param EntityManagerInterface       $entityManager                A Doctrine ORM entity manager.
     */
    public function __construct(
        LogActionItemEventDispatcher $logActionItemEventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * Dataset Restrictions Modifier UI.
     *
     *
     * @return Response
     */
    #[Route(path: '/dataset-restrictions', name: 'pelagos_app_ui_datasetrestrictions_default', methods: ['GET'])]
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
     * @param integer       $id            The entity ID of a Dataset.
     * @param EntityHandler $entityHandler A Pelagos entity handler.
     *
     *
     * @throws PersistenceException    Exception thrown when update fails.
     * @throws BadRequestHttpException Exception thrown when restriction key is null.
     * @return Response
     */
    #[Route(path: '/dataset-restrictions/{id}', name: 'pelagos_app_ui_datasetrestrictions_post', methods: ['POST'])]
    public function postAction(Request $request, int $id, EntityHandler $entityHandler, TokenStorageInterface $tokenStorage)
    {
        $restrictionKey = $request->request->get('restrictions');

        $datasets = $entityHandler->getBy(Dataset::class, array('id' => $id));

        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetSubmission = $dataset->getDatasetSubmission();
            $datasetStatus = $dataset->getDatasetStatus();

            if ($restrictionKey) {
                // Record the original state for logging purposes before changing it.
                $from = $datasetSubmission->getRestrictions();
                /** @var Account $account */
                $account = $tokenStorage->getToken()?->getUser();
                $actor = $account->getUserId();
                $this->dispatchLogRestrictionsEvent($dataset, $actor, $from, $restrictionKey);

                $datasetSubmission->setRestrictions($restrictionKey);

                try {
                    $entityHandler->update($datasetSubmission);
                } catch (PersistenceException $exception) {
                    throw new PersistenceException($exception->getMessage());
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
