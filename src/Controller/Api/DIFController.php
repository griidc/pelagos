<?php

namespace App\Controller\Api;

use App\Entity\Dataset;
use App\Entity\DIF;
use App\Event\EntityEventDispatcher;
use App\Form\DIFType;
use App\Security\Voter\DIFVoter;
use App\Util\Udi;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * The DIF api controller.
 */
class DIFController extends EntityController
{
    /**
     * Get a count of DIFs.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return integer
     */
    #[View]
    #[Route(path: '/api/difs/count', name: 'pelagos_api_difs_count', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function countAction(Request $request)
    {
        return $this->handleCount(DIF::class, $request);
    }

    /**
     * Get a collection of DIFs.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return array
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/difs', name: 'pelagos_api_difs_get_collection', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DIF::class, $request);
    }

    /**
     * Get a single DIF for a given id.
     *
     * @param integer $id The id of the DIF to return.
     *
     *
     *
     *
     * @return DIF
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/difs/{id}', name: 'pelagos_api_difs_get', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getAction(DIF $dif, SerializerInterface $serializer)
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

        return new Response($serializer->serialize($dif, 'json', $context));
    }

    /**
     * Create a new DIF from the submitted data.
     *
     * @param Request               $request               The request object.
     * @param EntityEventDispatcher $entityEventDispatcher The event Dispatcher.
     * @param Udi                   $udiUtil               Instance of UDI Utility.
     *
     *
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new DIF in the Location header.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs', name: 'pelagos_api_difs_post', methods: ['POST'], defaults: ['_format' => 'json'])]
    public function postAction(Request $request, EntityEventDispatcher $entityEventDispatcher, Udi $udiUtil)
    {
        // Create a new Dataset.
        $dataset = new Dataset();
        // Set the creator for the Dataset.
        $dataset->setCreator($this->getUser()->getPerson());
        // Create a new DIF for the Dataset.
        $dif = new DIF($dataset);
        // Handle the post (DIF will be created and Dataset creation will cascade).
        $this->handlePost(DIFType::class, DIF::class, $request, $dif);
        // Mint an UDI for the Dataset.
        $udi = $udiUtil->mintUdi($dataset);
        // Update the Dataset with the new UDI.
        $this->entityHandler->update($dataset);
        // If the "Save and Continue Later" button was pressed.
        if ($request->request->get('button') === 'save') {
            // Dispatch an event to indicate a DIF has been saved but not submitted.
            $entityEventDispatcher->dispatch($dif, 'saved_not_submitted');
        }
        // Return a created response, adding the UDI as a custom response header.
        return $this->makeCreatedResponse('pelagos_api_difs_get', $dif->getId(), array('X-UDI' => $udi));
    }

    /**
     * Replace a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to replace.
     * @param Request $request The request object.
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}', name: 'pelagos_api_difs_put', methods: ['PUT'], defaults: ['_format' => 'json'])]
    public function putAction(int $id, Request $request)
    {
        /** @var DIF $dif */
        $dif = $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PUT');
        // Update the Dataset too because Issue Tracker Ticket.
        $this->entityHandler->update($dif->getDataset());

        return $this->makeNoContentResponse();
    }

    /**
     * Update a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to update.
     * @param Request $request The request object.
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}', name: 'pelagos_api_difs_patch', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function patchAction(int $id, Request $request)
    {
        /** @var DIF $dif */
        $dif = $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PATCH');
        // Update the Dataset too because Issue Tracker Ticket.
        $this->entityHandler->update($dif->getDataset());

        return $this->makeNoContentResponse();
    }

    /**
     * Submit a DIF.
     *
     * @param integer $id The id of the DIF to submit.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to submit the DIF.
     * @throws BadRequestHttpException When the DIF could not be submitted.
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}/submit', name: 'pelagos_api_difs_submit', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function submitAction(int $id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to submit it.
        if (!$this->isGranted(DIFVoter::CAN_SUBMIT, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to submit this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to submit the DIF.
            $dif->submit();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch a 'submitted' event.
        $this->entityHandler->update($dif, 'submitted');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Approve a DIF.
     *
     * @param integer $id The id of the DIF to approve.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to approve the DIF.
     * @throws BadRequestHttpException When the DIF could not be approved.
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}/approve', name: 'pelagos_api_difs_approve', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function approveAction(int $id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to approve it.
        if (!$this->isGranted(DIFVoter::CAN_APPROVE, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to approve this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // If DIF was saved but not submitted and approval is attempted, submit it first then approve.
            if ($dif->getStatus() === DIF::STATUS_UNSUBMITTED) {
                $dif->submit();
            }
            // Try to approve the DIF.
            $dif->approve();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch an 'approved' event.
        $this->entityHandler->update($dif, 'approved');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Reject a DIF.
     *
     * @param integer $id The id of the DIF to reject.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to reject the DIF.
     * @throws BadRequestHttpException When the DIF could not be rejected.
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}/reject', name: 'pelagos_api_difs_reject', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function rejectAction(int $id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to reject it.
        if (!$this->isGranted(DIFVoter::CAN_REJECT, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to reject this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to reject the DIF.
            $dif->reject();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch a 'rejected' event.
        $this->entityHandler->update($dif, 'rejected');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Unlock a DIF.
     *
     * @param integer $id The id of the DIF to unlock.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to unlock the DIF.
     * @throws BadRequestHttpException When the DIF could not be unlocked.
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}/unlock', name: 'pelagos_api_difs_unlock', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function unlockAction(int $id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to unlock it.
        if (!$this->isGranted(DIFVoter::CAN_UNLOCK, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to unlock this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to unlock the DIF.
            $dif->unlock();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch an 'unlocked' event.
        $this->entityHandler->update($dif, 'unlocked');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Request a DIF be unlocked.
     *
     * @param integer               $id                    The id of the DIF to request unlock for.
     * @param EntityEventDispatcher $entityEventDispatcher The event dispatcher.
     *
     *
     *
     * @throws AccessDeniedHttpException When you do not have the permissions to unlock.
     * @throws BadRequestHttpException   When DIF can not be unlocked.
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: '/api/difs/{id}/request-unlock', name: 'pelagos_api_difs_request_unlock', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function requestUnlockAction(int $id, EntityEventDispatcher $entityEventDispatcher)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to request it be unlocked.
        if (!$this->isGranted(DIFVoter::CAN_REQUEST_UNLOCK, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to request this ' . $dif::FRIENDLY_NAME . ' be unlocked'
            );
        }
        // Check if the DIF is in an unlockable state.
        if (!$dif->isUnlockable()) {
            // Throw an exception if it's not.
            throw new BadRequestHttpException('This ' . $dif::FRIENDLY_NAME . ' cannot be unlocked');
        }
        // Dispatch an 'unlock_requested' event.
        $entityEventDispatcher->dispatch($dif, 'unlock_requested');
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }
}
