<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Person;
use Pelagos\Bundle\AppBundle\Form\PersonType;

/**
 * The Person api controller.
 */
class PersonController extends EntityController
{
    /**
     * Validate a value for a property of a Person.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
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
        return $this->validateProperty(PersonType::class, Person::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person.
     *
     * @param integer $id      The id of the existing Person.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Person was not found.",
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
        return $this->validateProperty(PersonType::class, Person::class, $request, $id);
    }

    /**
     * Get the distinct values for a property of a Person.
     *
     * @param string $property The property for which the distinct values are being requested.
     *
     * @ApiDoc(
     *   section = "People",
     *   statusCodes = {
     *     200 = "The list of distinct values was returned successfully.",
     *     400 = "An invalid property for Person was requested.",
     *     403 = "The authenticated user was not authorized to retrieve a list of
                  distinct values for properties of Person.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/getDistinctVals/{property}")
     *
     * @Rest\View()
     *
     * @return array The list of distinct values for a property.
     */
    public function getDistinctValsAction($property)
    {
        return $this->getDistinctVals(Person::class, $property);
    }

    /**
     * Get a collection of People.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
     *   parameters = {
     *     {"name"="properties", "dataType"="string", "required"=false, "format"="property1,property2,property3.subProperty,etc.", "description"="Return these properties"},
     *     {"name"="orderBy", "dataType"="string", "required"=false, "format"="property1,property2,property3.subProperty,etc.", "description"="Order by these properties"},
     *     {"name"="someProperty", "dataType"="string", "required"=false, "format"="value", "description"="Filter by someProperty"},
     *   },
     *   output = "array<Pelagos\Entity\Person>",
     *   statusCodes = {
     *     200 = "The requested collection of People was successfully retrieved.",
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
            $this->handleGetCollection(Person::class, $request)
        );
    }

    /**
     * Get a single Person for a given id.
     *
     * @param integer $id The id of the Person to return.
     *
     * @ApiDoc(
     *   section = "People",
     *   output = "Pelagos\Entity\Person",
     *   statusCodes = {
     *     200 = "The requested Person was successfully retrieved.",
     *     404 = "The requested Person was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Person
     */
    public function getAction($id)
    {
        return $this->handleGetOne(Person::class, $id);
    }

    /**
     * Create a new Person from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Person was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Person.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person in the Location header.
     */
    public function postAction(Request $request)
    {
        $person = $this->handlePost(PersonType::class, Person::class, $request);
        return $this->makeCreatedResponse('pelagos_api_people_get', $person->getId());
    }

    /**
     * Replace a Person with the submitted data.
     *
     * @param integer $id      The id of the Person to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person.",
     *     404 = "The requested Person was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(PersonType::class, Person::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Person with the submitted data.
     *
     * @param integer $id      The id of the Person to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "People",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person.",
     *     404 = "The requested Person was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(PersonType::class, Person::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person.
     *
     * @param integer $id The id of the Person to delete.
     *
     * @ApiDoc(
     *   section = "People",
     *   statusCodes = {
     *     204 = "The Person was successfully deleted.",
     *     403 = "You do not have sufficient privileges to delete this Person.",
     *     404 = "The requested Person was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(Person::class, $id);
        return $this->makeNoContentResponse();
    }
}
