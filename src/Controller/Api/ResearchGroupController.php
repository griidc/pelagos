<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Entity\ResearchGroup;
use App\Form\ResearchGroupType;

/**
 * The ResearchGroup api controller.
 */
class ResearchGroupController extends EntityController
{
    /**
     * Get a count of Research Groups.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Research Groups",
     *       "data_class": "Pelagos\Entity\ResearchGroup"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Research Groups was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/research-groups/count", name="pelagos_api_research_groups_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(ResearchGroup::class, $request);
    }

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
     * @Route(
     *     "/api/research-groups/validateProperty",
     *     name="pelagos_api_research_groups_validate_property",
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
     * @Route(
     *     "/api/research-groups/{id}/validateProperty",
     *     name="pelagos_api_research_groups_validate_property_existing",
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
     *     "class": "App\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Research Groups",
     *       "data_class": "App\Entity\ResearchGroup"
     *     }
     *   },
     *   output = "array<App\Entity\ResearchGroup>",
     *   statusCodes = {
     *     200 = "The requested collection of Research Groups was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/research-groups", name="pelagos_api_research_groups_get_collection", methods={"GET"})
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
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route(
     *     "/api/research-groups/{id}",
     *     name="pelagos_api_research_groups_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return ResearchGroup The Research Group that was retrieved.
     */
    public function getAction(int $id)
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
     * @Route("/api/research-groups", name="pelagos_api_research_groups_post", methods={"POST"})
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
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_put", methods={"PUT"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
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
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_patch", methods={"PATCH"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
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
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_delete", methods={"DELETE"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
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
     * @Route(
     *     "/api/research-groups/{id}/logo",
     *     name="pelagos_api_research_groups_get_logo",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction(int $id)
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
     * @Route(
     *     "/api/research-groups/{id}/logo",
     *     name="pelagos_api_research_groups_post_logo",
     *     methods={"POST"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction(int $id, Request $request)
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
     * @Route("/api/research-groups/{id}/logo", name="pelagos_api_research_groups_put_logo", methods={"PUT"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction(int $id, Request $request)
    {
        return $this->putProperty(ResearchGroup::class, $id, 'logo', $request);
    }
}
