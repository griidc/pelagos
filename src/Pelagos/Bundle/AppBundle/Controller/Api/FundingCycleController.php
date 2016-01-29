<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\FundingCycle;
use Pelagos\Bundle\AppBundle\Form\FundingCycleType;

/**
 * The FundingCycle api controller.
 */
class FundingCycleController extends EntityController
{
    /**
     * Get all funding cycles.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Funding Cycles",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\FundingCycle>",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @Annotations\Get("")
     *
     * @Annotations\View(serializerEnableMaxDepthChecks = true)
     *
     * @return array
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(FundingCycle::class, $request);
    }

    /**
     * Get a single funding cycle for a given id.
     *
     * @param integer $id The id of the funding cycle to return.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Funding Cycles",
     *   output = "Pelagos\Entity\FundingCycle",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the funding cycle is not found"
     *   }
     * )
     *
     * @Annotations\View(serializerEnableMaxDepthChecks = true)
     *
     * @return FundingCycle
     */
    public function getAction($id)
    {
        return $this->handleGetOne(FundingCycle::class, $id);
    }

    /**
     * Create a new funding cycle from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Funding Cycles",
     *   input = "FundingCycleType",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   statusCode = Codes::HTTP_CREATED,
     *   serializerEnableMaxDepthChecks = true
     * )
     *
     * @return FundingCycle|FormInterface
     */
    public function postAction(Request $request)
    {
        return $this->handlePost(FundingCycleType::class, FundingCycle::class, $request);
    }
}
