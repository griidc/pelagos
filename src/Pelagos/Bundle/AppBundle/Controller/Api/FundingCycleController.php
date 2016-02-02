<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Util\Codes;
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
     * Get a collection of funding cycles.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
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
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
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
     *   section = "Funding Cycles",
     *   output = "Pelagos\Entity\FundingCycle",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the funding cycle is not found"
     *   }
     * )
     *
     * @Rest\View()
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
     *   section = "Funding Cycles",
     *   input = {
     *     "class" = "Pelagos\Bundle\AppBundle\Form\FundingCycleType",
     *     "name" = ""
     *   },
     *   output = "Pelagos\Entity\FundingCycle",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View(
     *   statusCode = Codes::HTTP_CREATED
     * )
     *
     * @return FundingCycle|FormInterface
     */
    public function postAction(Request $request)
    {
        return $this->handlePost(FundingCycleType::class, FundingCycle::class, $request);
    }
}
