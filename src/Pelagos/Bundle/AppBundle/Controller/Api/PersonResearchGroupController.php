<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\PersonResearchGroup;
use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;

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
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Person to Research Group Associations",
     *       "data_class": "Pelagos\Entity\PersonResearchGroup"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Person to Research Group Associations was successfully returned.",
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
        return $this->handleCount(PersonResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of a Person to Research Group Association.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
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
        return $this->validateProperty(PersonResearchGroupType::class, PersonResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person to Research Group Association.
     *
     * @param integer $id      The id of the existing Person to Research Group Association.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Person to Research Group Association was not found.",
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
        return $this->validateProperty(PersonResearchGroupType::class, PersonResearchGroup::class, $request, $id);
    }

    /**
     * Get a collection of Person to Research Group Associations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Person to Research Group Associations",
     *       "data_class": "Pelagos\Entity\PersonResearchGroup"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\PersonResearchGroup>",
     *   statusCodes = {
     *     200 = "The requested collection of Person to Research Group Associations was successfully retrieved.",
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
        return $this->handleGetCollection(PersonResearchGroup::class, $request);
    }

    /**
     * Get a single Person to Research Group Association for a given id.
     *
     * @param integer $id The id of the Person to Research Group Association to return.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   output = "Pelagos\Entity\PersonResearchGroup",
     *   statusCodes = {
     *     200 = "The requested Person to Research Group Association was successfully retrieved.",
     *     404 = "The requested Person to Research Group Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return PersonResearchGroup
     */
    public function getAction($id)
    {
        return $this->handleGetOne(PersonResearchGroup::class, $id);
    }

    /**
     * Create a new Person to Research Group Association from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Person to Research Group Association was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Person to Research Group Association.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
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
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Research Group Association was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Research Group Association.",
     *     404 = "The requested Person to Research Group Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
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
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Person to Research Group Association was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person to Research Group Association.",
     *     404 = "The requested Person to Research Group Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(PersonResearchGroupType::class, PersonResearchGroup::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person to Research Group Association.
     *
     * @param integer $id The id of the Person to Research Group Association to delete.
     *
     * @ApiDoc(
     *   section = "Person to Research Group Associations",
     *   statusCodes = {
     *     204 = "The Person to Research Group Association was successfully deleted.",
     *     404 = "The requested Person to Research Group Association was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(PersonResearchGroup::class, $id);
        return $this->makeNoContentResponse();
    }
}
