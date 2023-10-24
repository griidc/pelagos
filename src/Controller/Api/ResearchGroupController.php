<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\ResearchGroup;
use App\Form\ResearchGroupType;
use App\Util\JsonSerializer;

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
     *
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
     *
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
     *
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
     *
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/research-groups", name="pelagos_api_research_groups_get_collection", methods={"GET"}, defaults={"_format"="json"})
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
     *
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
     *
     *
     * @Route("/api/research-groups", name="pelagos_api_research_groups_post", methods={"POST"}, defaults={"_format"="json"})
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
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_put", methods={"PUT"}, defaults={"_format"="json"})
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
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_patch", methods={"PATCH"}, defaults={"_format"="json"})
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
     *
     *
     * @Route("/api/research-groups/{id}", name="pelagos_api_research_groups_delete", methods={"DELETE"}, defaults={"_format"="json"})
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
     *
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
     *
     *
     * @Route(
     *     "/api/research-groups/{id}/logo",
     *     name="pelagos_api_research_groups_post_logo",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
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
     *
     *
     * @Route("/api/research-groups/{id}/logo", name="pelagos_api_research_groups_put_logo", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction(int $id, Request $request)
    {
        return $this->putProperty(ResearchGroup::class, $id, 'logo', $request);
    }

    /**
     * Gets the research group data as JSON for the Research Group Landing Page.
     *
     * @Route("/api/research-group/{id}", name="pelagos_api_research_group")
     *
     * @param ResearchGroup  $researchGroup  The research group for this request.
     * @param JsonSerializer $jsonSerializer The Pelagos JSON serializer.
     *
     * @return Response
     */
    public function getResearchGroupData(ResearchGroup $researchGroup, JsonSerializer $jsonSerializer): Response
    {
        $groups = array(
            'overview',
            'id',
            'projectDirectors' => array(
                'director', 'id'
            ),
            'fundingCycle' => array(
                'organization', 'id',
                'fundingOrganization' => array(
                    'organization', 'id',
                )
            ),
            'datasets' => array(
                'card', 'id',
                'doi' => array(
                    'doi',
                ),
                'publications',
                'datasetPublications' => array(
                    'publications',
                    'publication' => array(
                        'citation', 'id'
                    )
                ),
                'datasetSubmission' => array(
                    'authors',
                    'coldStorage',
                    'card',
                ),
                'dif' => array(
                    'card'
                ),
            ),
            'personResearchGroups' => array(
                'person',
                'person' => array (
                    'person',
                    'id',
                )
            )
        );

        return $jsonSerializer->serialize($researchGroup, $groups)->createJsonResponse();
    }
}
