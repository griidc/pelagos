<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\ResearchGroup;
use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController
{
    /**
     * Validate a value for a property of a Research Group.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"="true"}
     *   },
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
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request);
    }

    /**
     * Validate a value for a property of an existing Research Group.
     *
     * @param integer $id      The id of the existing Research Group.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Research Group was not found.",
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
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request, $id);
    }

    /**
     * Get a collection of Research Groups.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Research Groups",
     *       "data_class": "Pelagos\Entity\ResearchGroup"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\ResearchGroup>",
     *   statusCodes = {
     *     200 = "The requested collection of Research Groups was successfully retrieved.",
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
     * @ApiDoc(
     *   section = "Research Groups",
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "The requested Research Group was successfully retrieved.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return ResearchGroup The Research Group that was retrieved.
     */
    public function getAction($id)
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
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Research Group was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Research Group.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
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
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Research Group was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Research Group.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
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
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Research Group was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Research Group.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Research Group.
     *
     * @param integer $id The id of the Research Group to delete.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   statusCodes = {
     *     204 = "The Research Group was successfully deleted.",
     *     404 = "The requested Research Group was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(ResearchGroup::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Get the logo for a research group.
     *
     * @param integer $id The id of the research group to get the logo for.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   statusCodes = {
     *     200 = "Returned when successful.",
     *     404 = "Returned when the research group is not found or it does not have a logo."
     *   }
     * )
     *
     * @Rest\Get("/{id}/logo")
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction($id)
    {
        return $this->getProperty(ResearchGroup::class, $id, 'logo');
    }

    /**
     * Set or replace the logo of a research group via multipart/form-data POST.
     *
     * @param integer $id      The id of the research group to replace the logo for.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="logo", "dataType"="file", "required"="true"}
     *   },
     *   statusCodes = {
     *     204 = "Returned when the logo is successfully set or replaced.",
     *     404 = "Returned when the research group is not found."
     *   }
     * )
     *
     * @Rest\Post("/{id}/logo")
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction($id, Request $request)
    {
        return $this->postProperty(ResearchGroup::class, $id, 'logo', $request);
    }

    /**
     * Set or replace the logo of a research group via HTTP PUT file upload.
     *
     * @param integer $id      The id of the research group to replace the logo for.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   statusCodes = {
     *     204 = "Returned when the logo is successfully set or replaced.",
     *     404 = "Returned when the research group is not found."
     *   }
     * )
     *
     * @Rest\Put("/{id}/logo")
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction($id, Request $request)
    {
        return $this->putProperty(ResearchGroup::class, $id, 'logo', $request);
    }
}
