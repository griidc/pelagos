<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\ResearchGroup;
use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController
{
    /**
     * Validate a value for a property of a Research Group.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"="true"}
     *   },
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/validateProperty")
     *
     * @Rest\View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request);
    }

    /**
     * Get a collection of Research Groups.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\ResearchGroup>",
     *   statusCodes = {
     *     200 = "The requested collection of Research Groups was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return array The collection of Research Groups that was retrieved.
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(ResearchGroup::class, $request);
    }

    /**
     * Get a single Research Group for a given id.
     *
     * @param integer $id The id of the Research Group to return.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "The requested Research Group was successfully retrieved.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View()
     *
     * @return ResearchGroup The Research Group that was retrieved.
     */
    public function getAction($id)
    {
        return $this->handleGetOne(ResearchGroup::class, $id);
    }

    /**
     * Create a new Research Group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Research Group was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Research Group.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Research Group in the Location header.
     */
    public function postAction(Request $request)
    {
        $researchGroup = $this->handlePost(ResearchGroupType::class, ResearchGroup::class, $request);
        return new Response(
            null,
            Codes::HTTP_CREATED,
            array(
                'Location' => $this->generateUrl(
                    'pelagos_api_research_groups_get',
                    ['id' => $researchGroup->getId()]
                )
            )
        );
    }

    /**
     * Replace a Research Group with the submitted data.
     *
     * @param integer $id      The id of the Research Group to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Research Group was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Research Group.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PUT');
        return new Response(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Update a Research Group with the submitted data.
     *
     * @param integer $id      The id of the Research Group to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Research Group was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Research Group.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PATCH');
        return new Response(null, Codes::HTTP_NO_CONTENT);
    }
}
