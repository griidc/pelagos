<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use App\Form\NationalDataCenterType;
use App\Entity\NationalDataCenter;
use App\Entity\Entity;

/**
 * The API Controller Class for NationalDataCenter.
 */
class NationalDataCenterController extends EntityController
{
    /**
     * Get a count of National Data Centers.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return integer
     */
    #[View]
    #[Route(path: '/api/national-data-center/count', name: 'pelagos_api_national_data_center_count', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function countAction(Request $request)
    {
        return $this->handleCount(NationalDataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a National Data center.
     *
     * @param Request $request A Symfony request instance.
     *
     *
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
    #[Route(path: '/api/national-data-center/validateProperty', name: 'pelagos_api_national_data_center_validate_property', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(NationalDataCenterType::class, NationalDataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a existing National Data center.
     *
     * @param integer $id      The id of the existing National Data center.
     * @param Request $request A Symfony request instance.
     *
     *
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
    #[Route(path: '/api/national-data-center/{id}/validateProperty', name: 'pelagos_api_national_data_center_validate_property_existing', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(NationalDataCenterType::class, NationalDataCenter::class, $request, $id);
    }

    /**
     * Get a collection of National Datacenters.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return Response
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/national-data-center', name: 'pelagos_api_national_data_center_get_collection', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(NationalDataCenter::class, $request);
    }

    /**
     * Get a National data center for a given id.
     *
     * @param integer $id The id of the National Data center.
     *
     *
     *
     *
     * @return Entity(NationalDataCenter)
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/national-data-center/{id}', name: 'pelagos_api_national_data_center_get', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getAction(int $id)
    {
        return $this->handleGetOne(NationalDataCenter::class, $id);
    }

    /**
     * Create a new National Data Center.
     *
     * @param Request $request The Symfony request object.
     *
     *
     *
     *
     * @return Response A response object with empty body and status code.
     */
    #[Route(path: '/api/national-data-center', name: 'pelagos_api_national_data_center_post', methods: ['POST'], defaults: ['_format' => 'json'])]
    public function postAction(Request $request)
    {
        $nationalDataCenter = $this->handlePost(NationalDataCenterType::class, NationalDataCenter::class, $request);
        return $this->makeCreatedResponse('pelagos_api_national_data_center_get', $nationalDataCenter->getId());
    }

    /**
     * Replace a National Data Centerwith the submitted data.
     *
     * @param integer $id      The id of the National Data Center to replace.
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/national-data-center/{id}', name: 'pelagos_api_national_data_center_put', methods: ['PUT'], defaults: ['_format' => 'json'])]
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(NationalDataCenterType::class, NationalDataCenter::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a National Data Center with the submitted data.
     *
     * @param integer $id      The id of the National Data Center to update.
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/national-data-center/{id}', name: 'pelagos_api_national_data_center_patch', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(NationalDataCenterType::class, NationalDataCenter::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a National Data Center.
     *
     * @param integer $id The id of the National Data Center to delete.
     *
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/national-data-center/{id}', name: 'pelagos_api_national_data_center_delete', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteAction(int $id)
    {
        $this->handleDelete(NationalDataCenter::class, $id);
        return $this->makeNoContentResponse();
    }
}
