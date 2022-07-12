<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Entity\PersonFundingOrganization;
use App\Form\PersonFundingOrganizationType;

/**
 * The PersonFundingOrganization api controller.
 */
class PersonFundingOrganizationController extends EntityController
{
    /**
     * Get a count of Person to Funding Organization Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Get a count of Person to Funding Organization Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Person to Funding Organization Associations was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/count",
     *     name="pelagos_api_person_funding_organizations_count",
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
        return $this->handleCount(PersonFundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of a Person to Funding Organization Association.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Validate a value for a property of a Person to Funding Organization Association.",
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
     *     "/api/person-funding-organizations/validateProperty",
     *     name="pelagos_api_person_funding_organizations_validate_property",
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
        return $this->validateProperty(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Funding Organization Association.
     *
     * @param integer $id      The id of the existing Person to Funding Organization Association.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Validate a value for a property of an existing Person to Funding Organization Association.",
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
     *         description="The requested Person to Funding Organization Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/{id}/validateProperty",
     *     name="pelagos_api_person_funding_organizations_validate_property_existing",
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
        return $this->validateProperty(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $request, $id);
    }

    /**
     * Get a collection of Person to Funding Organization Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Get a collection of Person to Funding Organization Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Person to Funding Organization Associations was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations",
     *     name="pelagos_api_person_funding_organizations_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(PersonFundingOrganization::class, $request);
    }

    /**
     * Get a single Person to Funding Organization Association for a given id.
     *
     * @param integer $id The id of the Person to Funding Organization Association to return.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Get a single Person to Funding Organization Association for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Person to Funding Organization Association was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Funding Organization Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/{id}",
     *     name="pelagos_api_person_funding_organizations_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return PersonFundingOrganization
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(PersonFundingOrganization::class, $id);
    }

    /**
     * Create a new Person to Funding Organization Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Create a new Person to Funding Organization Association from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Person to Funding Organization Association was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Person to Funding Organization Association."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations",
     *     name="pelagos_api_person_funding_organizations_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person to Funding Organization Association in the Location header.
     */
    public function postAction(Request $request)
    {
        $personFundingOrganization = $this->handlePost(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $request);
        return $this->makeCreatedResponse('pelagos_api_person_funding_organizations_get', $personFundingOrganization->getId());
    }

    /**
     * Replace a Person to Funding Organization Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Funding Organization Association to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Replace a Person to Funding Organization Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Funding Organization Association was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Funding Organization Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Funding Organization Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/{id}",
     *     name="pelagos_api_person_funding_organizations_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Person to Funding Organization Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Funding Organization Association to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Update a Person to Funding Organization Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Funding Organization Association was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Funding Organization Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Funding Organization Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/{id}",
     *     name="pelagos_api_person_funding_organizations_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Funding Organization Association.
     *
     * @param integer $id The id of the Person to Funding Organization Association to delete.
     *
     * @Operation(
     *     tags={"Person to Funding Organization Associations"},
     *     summary="Delete a Person to Funding Organization Association.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Funding Organization Association was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to delete the Person to Funding Organization Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Funding Organization Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-funding-organizations/{id}",
     *     name="pelagos_api_person_funding_organizations_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(PersonFundingOrganization::class, $id);
        return $this->makeNoContentResponse();
    }
}
