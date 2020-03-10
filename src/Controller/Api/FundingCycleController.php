<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

use App\Entity\FundingCycle;
use App\Form\FundingCycleType;

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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Get a count of Funding Cycles.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Funding Cycles was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/funding-cycles/count", name="pelagos_api_funding_cycles_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Validate a value for a property of a Funding Cycle.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles/validateProperty",
     *     name="pelagos_api_funding_cycles_validate_property",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Validate a value for a property of an existing Funding Cycle.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Cycle was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles/{id}/validateProperty",
     *     name="pelagos_api_funding_cycles_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(FundingCycleType::class, FundingCycle::class, $request, $id);
    }

    /**
     * Get a collection of Funding Cycles.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Get a collection of Funding Cycles.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Funding Cycles was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles",
     *     name="pelagos_api_funding_cycles_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Get a single Funding Cycle for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Funding Cycle was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Cycle was not found."
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
     * @Route(
     *     "/api/funding-cycles/{id}",
     *     name="pelagos_api_funding_cycles_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return FundingCycle
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(FundingCycle::class, $id);
    }

    /**
     * Create a new Funding Cycle from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Create a new Funding Cycle from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Funding Cycle was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Funding Cycle."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles",
     *     name="pelagos_api_funding_cycles_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Replace a Funding Cycle with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Cycle was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Funding Cycle."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Cycle was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles/{id}",
     *     name="pelagos_api_funding_cycles_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
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
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Update a Funding Cycle with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Cycle was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Funding Cycle."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Cycle was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles/{id}",
     *     name="pelagos_api_funding_cycles_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(FundingCycleType::class, FundingCycle::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Funding Cycle.
     *
     * @param integer $id The id of the Funding Cycle to delete.
     *
     * @Operation(
     *     tags={"Funding Cycles"},
     *     summary="Delete a Funding Cycle.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Cycle was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to delete the Funding Cycle."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Cycle was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-cycles/{id}",
     *     name="pelagos_api_funding_cycles_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(FundingCycle::class, $id);
        return $this->makeNoContentResponse();
    }
}
