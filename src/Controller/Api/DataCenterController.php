<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
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
     *
     *
     * @Route("/api/data-center/count", name="pelagos_api_data_center_count", methods={"GET"}, defaults={"_format"="json"})
     *
     *
     * @return integer
     */
    #[View]
    public function countAction(Request $request)
    {
        return $this->handleCount(DataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a Data center.
     *
     * @param Request $request A Symfony request instance.
     *
     *
     *
     * @Route(
     *     "/api/data-center/validateProperty",
     *     name="pelagos_api_data_center_validate_property",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
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
     *
     *
     * @Route(
     *     "/api/data-center/{id}/validateProperty",
     *     name="pelagos_api_data_center_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(DataCenterType::class, DataCenter::class, $request, $id);
    }

    /**
     * Get a collection of Datacenters.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/data-center",
     *     name="pelagos_api_data_center_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     *
     * @return Response
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DataCenter::class, $request);
    }

    /**
     * Get a data center for a given id.
     *
     * @param integer $id The id of the Data center.
     *
     *
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     *
     * @return Entity(DataCenter)
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    public function getAction(int $id)
    {
        return $this->handleGetOne(DataCenter::class, $id);
    }

    /**
     * Create a new Data Center.
     *
     * @param Request $request The Symfony request object.
     *
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
