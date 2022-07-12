<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Entity\ResearchGroup;
use App\Form\ResearchGroupType;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController
{
    /**
     * Get a count of Research Groups.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Get a count of Research Groups.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Research Groups was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups/count", name="pelagos_api_research_groups_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(ResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of a Research Group.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Validate a value for a property of a Research Group.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/research-groups/validateProperty",
     *     name="pelagos_api_research_groups_validate_property",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of an existing Research Group.
     *
     * @param integer $id      The id of the existing Research Group.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Validate a value for a property of an existing Research Group.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Research Group was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/research-groups/{id}/validateProperty",
     *     name="pelagos_api_research_groups_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request, $id);
    }

    /**
     * Get a collection of Research Groups.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Get a collection of Research Groups.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Research Groups was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/research-groups", name="pelagos_api_research_groups_get_collection", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(
            ResearchGroup::class,
            $request,
            array('logo' => 'pelagos_api_research_groups_get_logo')
        );
    }

    /**
     * Get a single Research Group for a given id.
     *
     * @param integer $id The id of the Research Group to return.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Get a single Research Group for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Research Group was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Research Group was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route(
     *     "/api/research-groups/{id}",
     *     name="pelagos_api_research_groups_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return ResearchGroup The Research Group that was retrieved.
     */
    public function getAction(int $id)
    {
        $researchGroup = $this->handleGetOne(ResearchGroup::class, $id);
        if ($researchGroup instanceof ResearchGroup and $researchGroup->getLogo(true) !== null) {
            $researchGroup->setLogo(
                $this->getResourceUrl(
                    'pelagos_api_research_groups_get_logo',
                    $researchGroup->getId()
                )
            );
        }
        return $researchGroup;
    }

    /**
     * Create a new Research Group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Create a new Research Group from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Research Group was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Research Group."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups", name="pelagos_api_research_groups_post", methods={"POST"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Research Group in the Location header.
     */
    public function postAction(Request $request)
    {
        $researchGroup = $this->handlePost(ResearchGroupType::class, ResearchGroup::class, $request);
        return $this->makeCreatedResponse('pelagos_api_research_groups_get', $researchGroup->getId());
    }

    /**
     * Replace a Research Group with the submitted data.
     *
     * @param integer $id      The id of the Research Group to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Replace a Research Group with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Research Group was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Research Group."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Research Group was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_put", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Research Group with the submitted data.
     *
     * @param integer $id      The id of the Research Group to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Update a Research Group with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Research Group was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Research Group."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Research Group was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_patch", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Research Group.
     *
     * @param integer $id The id of the Research Group to delete.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Delete a Research Group.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Research Group was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Research Group was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_delete", methods={"DELETE"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(ResearchGroup::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Get the logo for a research group.
     *
     * @param integer $id The id of the research group to get the logo for.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Get the logo for a research group.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the research group is not found or it does not have a logo."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/research-groups/{id}/logo",
     *     name="pelagos_api_research_groups_get_logo",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction(int $id)
    {
        return $this->getProperty(ResearchGroup::class, $id, 'logo');
    }

    /**
     * Set or replace the logo of a research group via multipart/form-data POST.
     *
     * @param integer $id      The id of the research group to replace the logo for.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Set or replace the logo of a research group via multipart/form-data POST.",
     *     @SWG\Parameter(
     *         name="logo",
     *         in="formData",
     *         description="todo",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response="204",
     *         description="Returned when the logo is successfully set or replaced."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the research group is not found."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/research-groups/{id}/logo",
     *     name="pelagos_api_research_groups_post_logo",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction(int $id, Request $request)
    {
        return $this->postProperty(ResearchGroup::class, $id, 'logo', $request);
    }

    /**
     * Set or replace the logo of a research group via HTTP PUT file upload.
     *
     * @param integer $id      The id of the research group to replace the logo for.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Research Groups"},
     *     summary="Set or replace the logo of a research group via HTTP PUT file upload.",
     *     @SWG\Response(
     *         response="204",
     *         description="Returned when the logo is successfully set or replaced."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the research group is not found."
     *     )
     * )
     *
     *
     * @Route("/api/research-groups/{id}/logo", name="pelagos_api_research_groups_put_logo", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction(int $id, Request $request)
    {
        return $this->putProperty(ResearchGroup::class, $id, 'logo', $request);
    }
}
