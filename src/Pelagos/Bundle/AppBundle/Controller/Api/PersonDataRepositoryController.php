<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\PersonDataRepository;
use Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType;

/**
 * The PersonDataRepository api controller.
 */
class PersonDataRepositoryController extends EntityController
{
    /**
     * Validate a value for a property of a Person to Data Repository Association.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
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
        return $this->validateProperty(PersonDataRepositoryType::class, PersonDataRepository::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Data Repository Association.
     *
     * @param integer $id      The id of the existing Person to Data Repository Association.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Person to Data Repository Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/{id}/validateProperty")
     *
     * @Rest\View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction($id, Request $request)
    {
        return $this->validateProperty(PersonDataRepositoryType::class, PersonDataRepository::class, $request, $id);
    }

    /**
     * Get a collection of Person to Data Repository Associations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   parameters = {
     *     {"name"="properties", "dataType"="string", "required"=false, "format"="property1,property2,property3.subProperty,etc.", "description"="Return these properties"},
     *     {"name"="orderBy", "dataType"="string", "required"=false, "format"="property1,property2,property3.subProperty,etc.", "description"="Order by these properties"},
     *     {"name"="someProperty", "dataType"="string", "required"=false, "format"="value", "description"="Filter by someProperty"},
     *   },
     *   output = "array<Pelagos\Entity\PersonDataRepository>",
     *   statusCodes = {
     *     200 = "The requested collection of Person to Data Repository Associations was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->makeJsonResponse(
            $this->handleGetCollection(PersonDataRepository::class, $request)
        );
    }

    /**
     * Get a single Person to Data Repository Association for a given id.
     *
     * @param integer $id The id of the Person to Data Repository Association to return.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   output = "Pelagos\Entity\PersonDataRepository",
     *   statusCodes = {
     *     200 = "The requested Person to Data Repository Association was successfully retrieved.",
     *     404 = "The requested Person to Data Repository Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return PersonDataRepository
     */
    public function getAction($id)
    {
        return $this->handleGetOne(PersonDataRepository::class, $id);
    }

    /**
     * Create a new Person to Data Repository Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Person to Data Repository Association was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Person to Data Repository Association.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
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
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Data Repository Association was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Data Repository Association.",
     *     404 = "The requested Person to Data Repository Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
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
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Data Repository Association was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Data Repository Association.",
     *     404 = "The requested Person to Data Repository Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(PersonDataRepositoryType::class, PersonDataRepository::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Data Repository Association.
     *
     * @param integer $id The id of the Person to Data Repository Association to delete.
     *
     * @ApiDoc(
     *   section = "Person to Data Repository Associations",
     *   statusCodes = {
     *     204 = "The Person to Data Repository Association was successfully deleted.",
     *     403 = "The authenticated user was not authorized to delete the Person to Data Repository Association.",
     *     404 = "The requested Person to Data Repository Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(PersonDataRepository::class, $id);
        return $this->makeNoContentResponse();
    }
}
