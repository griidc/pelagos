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
     * Get a collection of Funding Cycles.
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
     *     200 = "The requested collection of Funding Cycles was successfully retrieved.",
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
     * @Rest\View()
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
        return new Response(
            null,
            Codes::HTTP_CREATED,
            array(
                'Location' => $this->generateUrl(
                    'pelagos_api_funding_cycles_get',
                    ['id' => $fundingCycle->getId()]
                ),
                'X-Entity-Id' => $fundingCycle->getId(),
            )
        );
    }
}
