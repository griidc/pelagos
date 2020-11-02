<?php

namespace App\Controller\Api;

use App\Entity\PersonResearchGroup;
use App\Form\PersonResearchGroupType;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The PersonResearchGroup api controller.
 */
class PersonResearchGroupController extends EntityController
{
    /**
     * Get a count of Person to Research Group Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Get a count of Person to Research Group Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Person to Research Group Associations was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups/count",
     *     name="pelagos_api_person_research_groups_count",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(PersonResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of a Person to Research Group Association.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Validate a value for a property of a Person to Research Group Association.",
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
     *     "/api/person-research-groups/validateProperty",
     *     name="pelagos_api_person_research_groups_validate_property",
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
        return $this->validateProperty(PersonResearchGroupType::class, PersonResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Research Group Association.
     *
     * @param integer $id      The id of the existing Person to Research Group Association.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Validate a value for a property of an existing Person to Research Group Association.",
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
     *         description="The requested Person to Research Group Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups/{id}/validateProperty",
     *     name="pelagos_api_person_research_groups_validate_property_existing",
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
        return $this->validateProperty(PersonResearchGroupType::class, PersonResearchGroup::class, $request, $id);
    }

    /**
     * Get a collection of Person to Research Group Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Get a collection of Person to Research Group Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Person to Research Group Associations was successfully retrieved."
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
     *     "/api/person-research-groups",
     *     name="pelagos_api_person_research_groups_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(PersonResearchGroup::class, $request);
    }

    /**
     * Get a single Person to Research Group Association for a given id.
     *
     * @param integer $id The id of the Person to Research Group Association to return.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Get a single Person to Research Group Association for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Person to Research Group Association was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Research Group Association was not found."
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
     *     "/api/person-research-groups/{id}",
     *     name="pelagos_api_person_research_groups_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return PersonResearchGroup
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(PersonResearchGroup::class, $id);
    }

    /**
     * Create a new Person to Research Group Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Create a new Person to Research Group Association from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Person to Research Group Association was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Person to Research Group Association."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups",
     *     name="pelagos_api_person_research_groups_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person to Research Group Association in the Location header.
     */
    public function postAction(Request $request)
    {
        $personResearchGroup = $this->handlePost(PersonResearchGroupType::class, PersonResearchGroup::class, $request);
        return $this->makeCreatedResponse('pelagos_api_person_research_groups_get', $personResearchGroup->getId());
    }

    /**
     * Replace a Person to Research Group Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Research Group Association to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Replace a Person to Research Group Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Research Group Association was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Research Group Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Research Group Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups/{id}",
     *     name="pelagos_api_person_research_groups_put",
     *      methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonResearchGroupType::class, PersonResearchGroup::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Person to Research Group Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Research Group Association to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Update a Person to Research Group Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Research Group Association was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Research Group Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Research Group Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups/{id}",
     *     name="pelagos_api_person_research_groups_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonResearchGroupType::class, PersonResearchGroup::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Research Group Association.
     *
     * @param integer $id The id of the Person to Research Group Association to delete.
     *
     * @Operation(
     *     tags={"Person to Research Group Associations"},
     *     summary="Delete a Person to Research Group Association.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Research Group Association was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Research Group Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-research-groups/{id}",
     *     name="pelagos_api_person_research_groups_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(PersonResearchGroup::class, $id);
        return $this->makeNoContentResponse();
    }
}
