<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

use FOS\RestBundle\Controller\Annotations\View;

use App\Entity\Dataset;
use App\Entity\DIF;

use App\Event\EntityEventDispatcher;

use App\Form\DIFType;
use App\Security\Voter\DIFVoter;
use App\Util\Udi;

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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Get a count of DIFs.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of DIFs was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @View()
     *
     * @Route("/api/difs/count", name="pelagos_api_difs_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(DIF::class, $request);
    }

    /**
     * Get a collection of DIFs.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Get a collection of DIFs.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="Filter by someProperty",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of DIFs was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs", name="pelagos_api_difs_get_collection", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return array
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DIF::class, $request);
    }

    /**
     * Get a single DIF for a given id.
     *
     * @param integer $id The id of the DIF to return.
     *
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Get a single DIF for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested DIF was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_get", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return DIF
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(DIF::class, $id);
    }

    /**
     * Create a new DIF from the submitted data.
     *
     * @param Request               $request               The request object.
     * @param EntityEventDispatcher $entityEventDispatcher The event Dispatcher.
     * @param Udi                   $udiUtil               Instance of UDI Utility.
     *
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Create a new DIF from the submitted data.",
     *     @SWG\Parameter(
     *         name="title",
     *         in="formData",
     *         description="Dataset Title:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="primaryPointOfContact",
     *         in="formData",
     *         description="Primary Data Point of Contact:",
     *         required=false,
     *         type="choice"
     *     ),
     *     @SWG\Parameter(
     *         name="secondaryPointOfContact",
     *         in="formData",
     *         description="Additional Data Point of Contact:",
     *         required=false,
     *         type="choice"
     *     ),
     *     @SWG\Parameter(
     *         name="abstract",
     *         in="formData",
     *         description="Dataset Abstract:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyEcologicalBiological",
     *         in="formData",
     *         description="Ecological/Biological",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyPhysicalOceanography",
     *         in="formData",
     *         description="Physical Oceanography",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyAtmospheric",
     *         in="formData",
     *         description="Atmospheric",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyChemical",
     *         in="formData",
     *         description="Chemical",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyHumanHealth",
     *         in="formData",
     *         description="Human Health",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudySocialCulturalPolitical",
     *         in="formData",
     *         description="Social/Cultural/Political",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyEconomics",
     *         in="formData",
     *         description="Economics",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="fieldOfStudyOther",
     *         in="formData",
     *         description="Other Field of Study:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dataSize",
     *         in="formData",
     *         description="Approximate Dataset Size:",
     *         required=false,
     *         type="choice"
     *     ),
     *     @SWG\Parameter(
     *         name="variablesObserved",
     *         in="formData",
     *         description="Data Parameters and Units:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodFieldSampling",
     *         in="formData",
     *         description="Field Sampling",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodSimulatedGenerated",
     *         in="formData",
     *         description="Simulated/Generated",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodLaboratory",
     *         in="formData",
     *         description="Laboratory",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodLiteratureBased",
     *         in="formData",
     *         description="Literature Based",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodRemoteSensing",
     *         in="formData",
     *         description="Remote Sensing",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="collectionMethodOther",
     *         in="formData",
     *         description="Other Collection Method:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="estimatedStartDate",
     *         in="formData",
     *         description="Start Date:",
     *         required=false,
     *         type="date"
     *     ),
     *     @SWG\Parameter(
     *         name="estimatedEndDate",
     *         in="formData",
     *         description="End Date:",
     *         required=false,
     *         type="date"
     *     ),
     *     @SWG\Parameter(
     *         name="spatialExtentDescription",
     *         in="formData",
     *         description="Description:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="spatialExtentGeometry",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveNODC",
     *         in="formData",
     *         description="National Centers for Environmental Information",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveStoret",
     *         in="formData",
     *         description="US EPA Storet",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveGBIF",
     *         in="formData",
     *         description="Global Biodiversity Information Facility",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveNCBI",
     *         in="formData",
     *         description="National Center for Biotechnology Information",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveDataGov",
     *         in="formData",
     *         description="Data.gov Dataset Management System",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nationalDataArchiveOther",
     *         in="formData",
     *         description="Other National Data Archive:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="ethicalIssues",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="choice"
     *     ),
     *     @SWG\Parameter(
     *         name="ethicalIssuesExplanation",
     *         in="formData",
     *         description="If yes or uncertain, please explain:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="remarks",
     *         in="formData",
     *         description="Remarks:",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="researchGroup",
     *         in="formData",
     *         description="Project Title:",
     *         required=false,
     *         type="choice"
     *     ),
     *     @SWG\Response(
     *         response="201",
     *         description="The DIF was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the DIF."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs", name="pelagos_api_difs_post", methods={"POST"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new DIF in the Location header.
     */
    public function postAction(Request $request, EntityEventDispatcher $entityEventDispatcher, Udi $udiUtil)
    {
        // Create a new Dataset.
        $dataset = new Dataset;
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Replace a DIF with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_put", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Update a DIF with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_patch", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PATCH');
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Submit a DIF.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully submitted."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The DIF could not be submitted (see error message for reason)."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="You do not have sufficient privileges to submit this DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}/submit", name="pelagos_api_difs_submit", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Approve a DIF.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully approved."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The DIF could not be approved (see error message for reason)."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="You do not have sufficient privileges to approve this DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}/approve", name="pelagos_api_difs_approve", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Reject a DIF.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully rejected."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The DIF could not be rejected (see error message for reason)."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="You do not have sufficient privileges to reject this DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}/reject", name="pelagos_api_difs_reject", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Unlock a DIF.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully unlocked."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The DIF could not be unlocked (see error message for reason)."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="You do not have sufficient privileges to unlock this DIF."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/difs/{id}/unlock", name="pelagos_api_difs_unlock", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
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
     * @Operation(
     *     tags={"DIFs"},
     *     summary="Request a DIF be unlocked.",
     *     @SWG\Response(
     *         response="204",
     *         description="The DIF was successfully requested to be unlocked."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The DIF cannot be requested to be unlocked (see error message for reason)."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested DIF was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @throws AccessDeniedHttpException When you do not have the permissions to unlock.
     * @throws BadRequestHttpException   When DIF can not be unlocked.
     *
     * @Route("/api/difs/{id}/request-unlock", name="pelagos_api_difs_request_unlock", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
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
