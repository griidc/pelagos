<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

use App\Form\DataCenterType;

use App\Entity\DataCenter;
use App\Entity\Entity;

/**
 * The API Controller Class for DataCenter.
 */
class DataCenterController extends EntityController
{
    /**
     * Get a count of Data Centers.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Get a count of Data Centers.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Data Centers was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/data-center/count", name="pelagos_api_data_center_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(DataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a Data center.
     *
     * @param Request $request A Symfony request instance.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Validate a value for a property of a Data center.",
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
     *     "/api/data-center/validateProperty",
     *     name="pelagos_api_data_center_validate_property",
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
        return $this->validateProperty(DataCenterType::class, DataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a existing Data center.
     *
     * @param integer $id      The id of the existing Data center.
     * @param Request $request A Symfony request instance.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Validate a value for a property of a existing Data center.",
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
     *         description="The requested Data center was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center/{id}/validateProperty",
     *     name="pelagos_api_data_center_validate_property_existing",
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
        return $this->validateProperty(DataCenterType::class, DataCenter::class, $request, $id);
    }

    /**
     * Get a collection of Datacenters.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Get a collection of Datacenters.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Data Centers was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center",
     *     name="pelagos_api_data_center_get_collection",
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
        return $this->handleGetCollection(DataCenter::class, $request);
    }

    /**
     * Get a data center for a given id.
     *
     * @param integer $id The id of the Data center.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Get a data center for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="Successfully retrieved the Data Center."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Data Center was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Entity(DataCenter)
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(DataCenter::class, $id);
    }

    /**
     * Create a new Data Center.
     *
     * @param Request $request The Symfony request object.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Create a new Data Center.",
     *     @SWG\Response(
     *         response="201",
     *         description="Successfully created a new Data Center."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Data Center."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center",
     *     name="pelagos_api_data_center_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with empty body and status code.
     */
    public function postAction(Request $request)
    {
        $dataCenter = $this->handlePost(DataCenterType::class, DataCenter::class, $request);
        return $this->makeCreatedResponse('pelagos_api_data_center_get', $dataCenter->getId());
    }

    /**
     * Replace a Data Center with the submitted data.
     *
     * @param integer $id      The id of the Data Center to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Replace a Data Center with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Data Center was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Data Center."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Data Center was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(DataCenterType::class, DataCenter::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Data Center with the submitted data.
     *
     * @param integer $id      The id of the Data Center to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Update a Data Center with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Data Center was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Data Center."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Data Center was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(DataCenterType::class, DataCenter::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Data Center.
     *
     * @param integer $id The id of the Data Center to delete.
     *
     * @Operation(
     *     tags={"Data Centers"},
     *     summary="Delete a Data Center.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Data Center was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Data Center was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(DataCenter::class, $id);
        return $this->makeNoContentResponse();
    }
}
