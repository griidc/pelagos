<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\PersonResearchGroup;
use App\Form\PersonResearchGroupType;

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
