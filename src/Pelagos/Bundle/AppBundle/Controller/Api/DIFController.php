<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\DIF;
use Pelagos\Bundle\AppBundle\Form\DIFType;

/**
 * The DIF api controller.
 */
class DIFController extends EntityController
{
    /**
     * Get a collection of DIFs.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\DIF>",
     *   statusCodes = {
     *     200 = "The requested collection of DIFs was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
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
     * @ApiDoc(
     *   section = "DIFs",
     *   output = "Pelagos\Entity\DIF",
     *   statusCodes = {
     *     200 = "The requested DIF was successfully retrieved.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return DIF
     */
    public function getAction($id)
    {
        return $this->handleGetOne(DIF::class, $id);
    }

    /**
     * Create a new DIF from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     201 = "The DIF was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the DIF.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new DIF in the Location header.
     */
    public function postAction(Request $request)
    {
        $person = $this->handlePost(DIFType::class, DIF::class, $request);
        return $this->makeCreatedResponse('pelagos_api_people_get', $person->getId());
    }

    /**
     * Replace a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     204 = "The DIF was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
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
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     204 = "The DIF was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a DIF.
     *
     * @param integer $id The id of the DIF to delete.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully deleted.",
     *     403 = "You do not have sufficient privileges to delete this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(DIF::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Submit a DIF.
     *
     * @param integer $id The id of the DIF to submit.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully submitted.",
     *     403 = "You do not have sufficient privileges to submit this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function submitAction($id)
    {
        $dif = $this->handleGetOne(DIF::class, $id);
        $dif->setStatus(DIF::STATUS_SUBMITTED);
        return $this->makeNoContentResponse();
    }

    /**
     * Approve a DIF.
     *
     * @param integer $id The id of the DIF to approve.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully approved.",
     *     403 = "You do not have sufficient privileges to approve this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function approveAction($id)
    {
        $dif = $this->handleGetOne(DIF::class, $id);
        $dif->setStatus(DIF::STATUS_APPROVED);
        return $this->makeNoContentResponse();
    }

    /**
     * Reject a DIF.
     *
     * @param integer $id The id of the DIF to reject.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully rejected.",
     *     403 = "You do not have sufficient privileges to reject this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function rejectAction($id)
    {
        $dif = $this->handleGetOne(DIF::class, $id);
        $dif->setStatus(DIF::STATUS_UNSUBMITTED);
        return $this->makeNoContentResponse();
    }

    /**
     * Unlock a DIF.
     *
     * @param integer $id The id of the DIF to unlock.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully unlocked.",
     *     403 = "You do not have sufficient privileges to unlock this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function unlockAction($id)
    {
        $dif = $this->handleGetOne(DIF::class, $id);
        $dif->setStatus(DIF::STATUS_UNSUBMITTED);
        return $this->makeNoContentResponse();
    }
}
