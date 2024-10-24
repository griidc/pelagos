<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
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
     *
     *
     *
     * @return integer
     */
    #[View]
    #[Route(path: '/api/funding-cycles/count', name: 'pelagos_api_funding_cycles_count', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function countAction(Request $request)
    {
        return $this->handleCount(FundingCycle::class, $request);
    }

    /**
     * Validate a value for a property of a Funding Cycle.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
    #[Route(path: '/api/funding-cycles/validateProperty', name: 'pelagos_api_funding_cycles_validate_property', methods: ['GET'], defaults: ['_format' => 'json'])]
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
     *
     *
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    #[View]
    #[Route(path: '/api/funding-cycles/{id}/validateProperty', name: 'pelagos_api_funding_cycles_validate_property_existing', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(FundingCycleType::class, FundingCycle::class, $request, $id);
    }

    /**
     * Get a collection of Funding Cycles.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return Response
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/funding-cycles', name: 'pelagos_api_funding_cycles_get_collection', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(FundingCycle::class, $request);
    }

    /**
     * Get a single Funding Cycle for a given id.
     *
     * @param integer $id The id of the Funding Cycle to return.
     *
     *
     *
     *
     * @return FundingCycle
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/funding-cycles/{id}', name: 'pelagos_api_funding_cycles_get', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getAction(int $id)
    {
        return $this->handleGetOne(FundingCycle::class, $id);
    }

    /**
     * Create a new Funding Cycle from the submitted data.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Funding Cycle in the Location header.
     */
    #[Route(path: '/api/funding-cycles', name: 'pelagos_api_funding_cycles_post', methods: ['POST'], defaults: ['_format' => 'json'])]
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
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/funding-cycles/{id}', name: 'pelagos_api_funding_cycles_put', methods: ['PUT'], defaults: ['_format' => 'json'])]
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
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/funding-cycles/{id}', name: 'pelagos_api_funding_cycles_patch', methods: ['PATCH'], defaults: ['_format' => 'json'])]
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
     *
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/funding-cycles/{id}', name: 'pelagos_api_funding_cycles_delete', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteAction(int $id)
    {
        $this->handleDelete(FundingCycle::class, $id);
        return $this->makeNoContentResponse();
    }
}
