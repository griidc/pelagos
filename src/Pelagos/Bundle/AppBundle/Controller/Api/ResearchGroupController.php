<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\ResearchGroup;
use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController
{
    /**
     * Get all research groups.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\ResearchGroup>",
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
        return $this->handleGetCollection(ResearchGroup::class, $request);
    }

    /**
     * Get a single research group for a given id.
     *
     * @param integer $id The id of the research group to return.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Groups",
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the research group is not found"
     *   }
     * )
     *
     * @Annotations\View(serializerEnableMaxDepthChecks = true)
     *
     * @return ResearchGroup
     */
    public function getAction($id)
    {
        return $this->handleGetOne(ResearchGroup::class, $id);
    }

    /**
     * Create a new research group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Groups",
     *   input = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType",
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
     * @return ResearchGroup|FormTypeInterface
     */
    public function postAction(Request $request)
    {
        return $this->handlePost(ResearchGroupType::class, ResearchGroup::class, $request);
    }
}
