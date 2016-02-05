<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FOS\RestBundle\Util\Codes;
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
     * Validate a value for a property of a research group.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
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
        return $this->validateProperty(ResearchGroupType::class, ResearchGroup::class, $request);
    }

    /**
     * Get a collection of research groups.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\ResearchGroup>",
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
        $researchGroups = $this->handleGetCollection(ResearchGroup::class, $request);
        foreach ($researchGroups as $researchGroup) {
            $this->setLogoToResourceUrl($researchGroup);
        }
        return $researchGroups;
    }

    /**
     * Get a single research group for a given id.
     *
     * @param integer $id The id of the research group to return.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the research group is not found"
     *   }
     * )
     *
     * @Rest\View()
     *
     * @return ResearchGroup
     */
    public function getAction($id)
    {
        return $this->setLogoToResourceUrl($this->handleGetOne(ResearchGroup::class, $id));
    }

    /**
     * Create a new research group from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {
     *     "class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType",
     *     "name" = ""
     *   },
     *   output = "Pelagos\Entity\ResearchGroup",
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
     * @return ResearchGroup|FormInterface
     */
    public function postAction(Request $request)
    {
        return $this->handlePost(ResearchGroupType::class, ResearchGroup::class, $request);
    }

    /**
     * Replace a research group with the submitted data.
     *
     * @param integer $id      The id of the research group to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {
     *     "class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType",
     *     "name" = ""
     *   },
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View()
     *
     * @return ResearchGroup|FormInterface
     */
    public function putAction($id, Request $request)
    {
        return $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PUT');
    }

    /**
     * Update a research group with the submitted data.
     *
     * @param integer $id      The id of the research group to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Research Groups",
     *   input = {
     *     "class" = "Pelagos\Bundle\AppBundle\Form\ResearchGroupType",
     *     "name" = ""
     *   },
     *   output = "Pelagos\Entity\ResearchGroup",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Rest\View()
     *
     * @return ResearchGroup|FormInterface
     */
    public function patchAction($id, Request $request)
    {
        return $this->handleUpdate(ResearchGroupType::class, ResearchGroup::class, $id, $request, 'PATCH');
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

    /**
     * Set the logo attribute of a research group to be the logo resource URL.
     *
     * @param ResearchGroup $researchGroup The research group to update.
     *
     * @return ResearchGroup The research group with it's logo set to the logo resource URL.
     */
    private function setLogoToResourceUrl(ResearchGroup $researchGroup)
    {
        if ($researchGroup->getLogo(true) !== null) {
            $researchGroup->setLogo(
                $this->generateUrl(
                    'pelagos_api_research_groups_get_logo',
                    array('id' => $researchGroup->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }
        return $researchGroup;
    }
}
