<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\PersonFundingOrganization;
use Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType;

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
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Person to Funding Organization Associations",
     *       "data_class": "Pelagos\Entity\PersonFundingOrganization"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Person to Funding Organization Associations was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/count")
     *
     * @Rest\View()
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
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
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
        return $this->validateProperty(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Funding Organization Association.
     *
     * @param integer $id      The id of the existing Person to Funding Organization Association.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Person to Funding Organization Association was not found.",
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
        return $this->validateProperty(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $request, $id);
    }

    /**
     * Get a collection of Person to Funding Organization Associations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Person to Funding Organization Associations",
     *       "data_class": "Pelagos\Entity\PersonFundingOrganization"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\PersonFundingOrganization>",
     *   statusCodes = {
     *     200 = "The requested collection of Person to Funding Organization Associations was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
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
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   output = "Pelagos\Entity\PersonFundingOrganization",
     *   statusCodes = {
     *     200 = "The requested Person to Funding Organization Association was successfully retrieved.",
     *     404 = "The requested Person to Funding Organization Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return PersonFundingOrganization
     */
    public function getAction($id)
    {
        return $this->handleGetOne(PersonFundingOrganization::class, $id);
    }

    /**
     * Create a new Person to Funding Organization Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Person to Funding Organization Association was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Person to Funding Organization Association.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
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
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Funding Organization Association was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Funding Organization Association.",
     *     404 = "The requested Person to Funding Organization Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
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
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Funding Organization Association was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Funding Organization Association.",
     *     404 = "The requested Person to Funding Organization Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(PersonFundingOrganizationType::class, PersonFundingOrganization::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Funding Organization Association.
     *
     * @param integer $id The id of the Person to Funding Organization Association to delete.
     *
     * @ApiDoc(
     *   section = "Person to Funding Organization Associations",
     *   statusCodes = {
     *     204 = "The Person to Funding Organization Association was successfully deleted.",
     *     403 = "The authenticated user was not authorized to delete the Person to Funding Organization Association.",
     *     404 = "The requested Person to Funding Organization Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(PersonFundingOrganization::class, $id);
        return $this->makeNoContentResponse();
    }
}
