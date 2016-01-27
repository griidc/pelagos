<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\ResearchGroup;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController implements ClassResourceInterface
{
    /**
     * Get all research groups.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Group",
     *   output = "array<Pelagos\Entity\ResearchGroup>",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @Annotations\Get("/research_groups")
     *
     * @Annotations\View()
     *
     * @return array
     */
    public function cgetAction()
    {
        return parent::cgetAction();
    }

    /**
     * Get a single research group for a given id.
     *
     * @param integer $id The id of the research group to return.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Group",
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the research group is not found"
     *   }
     * )
     *
     * @Annotations\Get("/research_groups/{id}")
     *
     * @Annotations\View()
     *
     * @return ResearchGroup
     */
    public function getAction($id)
    {
        return parent::getAction($id);
    }

    /**
     * Create a new research group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Group",
     *   input = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\Post("/research_groups")
     *
     * @Annotations\View(
     *   statusCode = Codes::HTTP_CREATED
     * )
     *
     * @return ResearchGroup|FormTypeInterface
     */
    public function postAction(Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * Presents a form that can be used to create a new research group.
     *
     * @ApiDoc(
     *   resource = true,
     *   section = "Research Group",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\Get("/research_groups/new")
     *
     * @Annotations\View(
     *  templateVar = "form"
     * )
     *
     * @return FormTypeInterface
     */
    public function newAction()
    {
        return parent::newAction();
    }
}
