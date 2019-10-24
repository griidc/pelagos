<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\FundingCycle;
use Pelagos\Bundle\AppBundle\Form\FundingCycleType;

/**
 * The FundingCycle api controller.
 */
class FundingCycleController extends EntityController
{
    /**
     * Get a count of Funding Cycles.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Funding Cycles",
     *       "data_class": "Pelagos\Entity\FundingCycle"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Funding Cycles was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/count")
     *
     * @Rest\View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(FundingCycle::class, $request);
    }

    /**
     * Validate a value for a property of a Funding Cycle.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/validateProperty")
     *
     * @Rest\View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(FundingCycleType::class, FundingCycle::class, $request);
    }

    /**
     * Validate a value for a property of an existing Funding Cycle.
     *
     * @param integer $id      The id of the existing Funding Cycle.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Funding Cycle was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/{id}/validateProperty")
     *
     * @Rest\View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction($id, Request $request)
    {
        return $this->validateProperty(FundingCycleType::class, FundingCycle::class, $request, $id);
    }

    /**
     * Get a collection of Funding Cycles.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Funding Cycles",
     *       "data_class": "Pelagos\Entity\FundingCycle"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\FundingCycle>",
     *   statusCodes = {
     *     200 = "The requested collection of Funding Cycles was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(FundingCycle::class, $request);
    }

    /**
     * Get a single Funding Cycle for a given id.
     *
     * @param integer $id The id of the Funding Cycle to return.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   output = "Pelagos\Entity\FundingCycle",
     *   statusCodes = {
     *     200 = "The requested Funding Cycle was successfully retrieved.",
     *     404 = "The requested Funding Cycle was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return FundingCycle
     */
    public function getAction($id)
    {
        return $this->handleGetOne(FundingCycle::class, $id);
    }

    /**
     * Create a new Funding Cycle from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingCycleType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Funding Cycle was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Funding Cycle.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Funding Cycle in the Location header.
     */
    public function postAction(Request $request)
    {
        $fundingCycle = $this->handlePost(FundingCycleType::class, FundingCycle::class, $request);
        return $this->makeCreatedResponse('pelagos_api_funding_cycles_get', $fundingCycle->getId());
    }

    /**
     * Replace a Funding Cycle with the submitted data.
     *
     * @param integer $id      The id of the Funding Cycle to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingCycleType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Funding Cycle was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Funding Cycle.",
     *     404 = "The requested Funding Cycle was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(FundingCycleType::class, FundingCycle::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Funding Cycle with the submitted data.
     *
     * @param integer $id      The id of the Funding Cycle to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingCycleType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Funding Cycle was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Funding Cycle.",
     *     404 = "The requested Funding Cycle was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(FundingCycleType::class, FundingCycle::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Funding Cycle.
     *
     * @param integer $id The id of the Funding Cycle to delete.
     *
     * @ApiDoc(
     *   section = "Funding Cycles",
     *   statusCodes = {
     *     204 = "The Funding Cycle was successfully deleted.",
     *     403 = "The authenticated user was not authorized to delete the Funding Cycle.",
     *     404 = "The requested Funding Cycle was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(FundingCycle::class, $id);
        return $this->makeNoContentResponse();
    }
}
