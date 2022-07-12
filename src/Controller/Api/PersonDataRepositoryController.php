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
use App\Entity\PersonDataRepository;
use App\Form\PersonDataRepositoryType;

/**
 * The PersonDataRepository api controller.
 */
class PersonDataRepositoryController extends EntityController
{
    /**
     * Get a count of Person to Data Repository Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Get a count of Person to Data Repository Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Person to Data Repository Associations was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/count",
     *     name="pelagos_api_person_data_repositories_count",
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
        return $this->handleCount(PersonDataRepository::class, $request);
    }

    /**
     * Validate a value for a property of a Person to Data Repository Association.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Validate a value for a property of a Person to Data Repository Association.",
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
     *     "/api/person-data-repositories/validateProperty",
     *     name="pelagos_api_person_data_repositories_validate_property",
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
        return $this->validateProperty(PersonDataRepositoryType::class, PersonDataRepository::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Data Repository Association.
     *
     * @param integer $id      The id of the existing Person to Data Repository Association.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Validate a value for a property of an existing Person to Data Repository Association.",
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
     *         description="The requested Person to Data Repository Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/{id}/validateProperty",
     *     name="pelagos_api_person_data_repositories_validate_property_existing",
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
        return $this->validateProperty(PersonDataRepositoryType::class, PersonDataRepository::class, $request, $id);
    }

    /**
     * Get a collection of Person to Data Repository Associations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Get a collection of Person to Data Repository Associations.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Person to Data Repository Associations was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories",
     *     name="pelagos_api_person_data_repositories_get_collection",
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
        return $this->handleGetCollection(PersonDataRepository::class, $request);
    }

    /**
     * Get a single Person to Data Repository Association for a given id.
     *
     * @param integer $id The id of the Person to Data Repository Association to return.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Get a single Person to Data Repository Association for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Person to Data Repository Association was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Data Repository Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/{id}",
     *     name="pelagos_api_person_data_repositories_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return PersonDataRepository
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(PersonDataRepository::class, $id);
    }

    /**
     * Create a new Person to Data Repository Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Create a new Person to Data Repository Association from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Person to Data Repository Association was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Person to Data Repository Association."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories",
     *     name="pelagos_api_person_data_repositories_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person to Data Repository Association in the Location header.
     */
    public function postAction(Request $request)
    {
        $personDataRepository = $this->handlePost(PersonDataRepositoryType::class, PersonDataRepository::class, $request);
        return $this->makeCreatedResponse('pelagos_api_person_data_repositories_get', $personDataRepository->getId());
    }

    /**
     * Replace a Person to Data Repository Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Data Repository Association to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Replace a Person to Data Repository Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Data Repository Association was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Data Repository Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Data Repository Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/{id}",
     *     name="pelagos_api_person_data_repositories_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonDataRepositoryType::class, PersonDataRepository::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Person to Data Repository Association with the submitted data.
     *
     * @param integer $id      The id of the Person to Data Repository Association to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Update a Person to Data Repository Association with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Data Repository Association was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Person to Data Repository Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Data Repository Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/{id}",
     *     name="pelagos_api_person_data_repositories_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonDataRepositoryType::class, PersonDataRepository::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Data Repository Association.
     *
     * @param integer $id The id of the Person to Data Repository Association to delete.
     *
     * @Operation(
     *     tags={"Person to Data Repository Associations"},
     *     summary="Delete a Person to Data Repository Association.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Person to Data Repository Association was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to delete the Person to Data Repository Association."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Person to Data Repository Association was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/person-data-repositories/{id}",
     *     name="pelagos_api_person_data_repositories_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(PersonDataRepository::class, $id);
        return $this->makeNoContentResponse();
    }
}
