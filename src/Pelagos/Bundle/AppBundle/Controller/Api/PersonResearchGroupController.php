<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Util\Codes;
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
     * Validate a value for a property of a person research group.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"="true"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when validation is successful (regardless of validity)",
     *     400 = "Returned when bad parameters are passed in the query string"
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
     * Get a collection of person research groups.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\PersonResearchGroup>",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return array
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(PersonResearchGroup::class, $request);
    }

    /**
     * Get a single person research group for a given id.
     *
     * @param integer $id The id of the person research group to return.
     *
     * @ApiDoc(
     *   section = "Person Research Groups",
     *   output = "Pelagos\Entity\PersonResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the person research group is not found"
     *   }
     * )
     *
     * @Rest\View()
     *
     * @return PersonResearchGroup
     */
    public function getAction($id)
    {
        return $this->handleGetOne(PersonResearchGroup::class, $id);
    }

    /**
     * Create a new person research group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Person Research Groups",
     *   input = {
     *     "class" = "Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType",
     *     "name" = ""
     *   },
     *   output = "Pelagos\Entity\PersonResearchGroup",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View(
     *   statusCode = Codes::HTTP_CREATED
     * )
     *
     * @return PersonResearchGroup|FormInterface
     */
    public function postAction(Request $request)
    {
        return $this->handlePost(PersonResearchGroupType::class, PersonResearchGroup::class, $request);
    }

    /**
     * Delete a person research group.
     *
     * @param integer $id The id of the person research group to delete.
     *
     * @ApiDoc(
     *   section = "Person Research Groups",
     *   statusCodes = {
     *     204 = "Returned when the person research group is successfully deleted.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        return $this->handleDelete(PersonResearchGroup::class, $id);
    }
}
