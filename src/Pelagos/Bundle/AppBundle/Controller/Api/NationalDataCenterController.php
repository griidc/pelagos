<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\NationalDataCenterType;

use Pelagos\Entity\NationalDataCenter;
use Pelagos\Entity\Entity;


/**
 * The API Controller Class for NationalDataCenter.
 */
class NationalDataCenterController extends EntityController
{
    /**
     * Get a National data center for a given id.
     *
     * @param $id string The id of the National Data center.
     *
     * @ApiDoc(
     *     section = "National Data Center",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *     statusCodes = {
     *       200 = "Successfully retrieved the National Data Center.",
     *       404 = "The requested National Data Center was not found.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Entity(NationalDataCenter)
     */
    public function getAction($id)
    {
        return $this->handleGetOne(NationalDataCenter::class, $id);
    }
    /**
     * Create a new National Data Center.
     *
     * @param Request $request The Symfony request object.
     *
     * @ApiDoc(
     *     section = "National Data Center",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *     statusCodes = {
     *       201 = "Successfully created a new National Data Center.",
     *       400 = "The request could not be processed due to validation or other errors.",
     *       403 = "The authenticated user was not authorized to create the National Data Center.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     *
     * @return Response A response object with empty body and status code.
     */
    public function postAction(Request $request)
    {
        $nationalDataCenter = $this->handlePost(NationalDataCenterType::class, NationalDataCenter::class, $request);
        return $this->makeCreatedResponse('pelagos_api_national_data_center_get', $nationalDataCenter->getId());
    }
}