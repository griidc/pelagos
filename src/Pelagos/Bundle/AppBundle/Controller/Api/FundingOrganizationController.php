<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\FundingOrganization;
use Pelagos\Bundle\AppBundle\Form\FundingOrganizationType;

/**
 * The FundingOrganization api controller.
 */
class FundingOrganizationController extends EntityController
{
    /**
     * Validate a value for a property of a Funding Organization.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
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
        return $this->validateProperty(FundingOrganizationType::class, FundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of an existing Funding Organization.
     *
     * @param integer $id      The id of the existing Funding Organization.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Funding Organization was not found.",
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
        return $this->validateProperty(FundingOrganizationType::class, FundingOrganization::class, $request, $id);
    }

    /**
     * Get a collection of Funding Organizations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Funding Organizations",
     *       "data_class": "Pelagos\Entity\FundingOrganization"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\FundingOrganization>",
     *   statusCodes = {
     *     200 = "The requested collection of Funding Organizations was successfully retrieved.",
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
            FundingOrganization::class,
            $request,
            array('logo' => 'pelagos_api_funding_organizations_get_logo')
        );
    }

    /**
     * Get a single Funding Organization for a given id.
     *
     * @param integer $id The id of the Funding Organization to return.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   output = "Pelagos\Entity\FundingOrganization",
     *   statusCodes = {
     *     200 = "The requested Funding Organization was successfully retrieved.",
     *     404 = "The requested Funding Organization was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return FundingOrganization The Funding Organization that was retrieved.
     */
    public function getAction($id)
    {
        $fundingOrganization = $this->handleGetOne(FundingOrganization::class, $id);
        if ($fundingOrganization instanceof FundingOrganization and $fundingOrganization->getLogo(true) !== null) {
            $fundingOrganization->setLogo(
                $this->getResourceUrl(
                    'pelagos_api_funding_organizations_get_logo',
                    $fundingOrganization->getId()
                )
            );
        }
        return $fundingOrganization;
    }

    /**
     * Create a new Funding Organization from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Funding Organization was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Funding Organization.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Funding Organization in the Location header.
     */
    public function postAction(Request $request)
    {
        $fundingOrganization = $this->handlePost(FundingOrganizationType::class, FundingOrganization::class, $request);
        return $this->makeCreatedResponse('pelagos_api_funding_organizations_get', $fundingOrganization->getId());
    }

    /**
     * Replace a Funding Organization with the submitted data.
     *
     * @param integer $id      The id of the Funding Organization to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Funding Organization was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Funding Organization.",
     *     404 = "The requested Funding Organization was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(FundingOrganizationType::class, FundingOrganization::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Funding Organization with the submitted data.
     *
     * @param integer $id      The id of the Funding Organization to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\FundingOrganizationType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Funding Organization was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Funding Organization.",
     *     404 = "The requested Funding Organization was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(FundingOrganizationType::class, FundingOrganization::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Funding Organization.
     *
     * @param integer $id The id of the Funding Organization to delete.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   statusCodes = {
     *     204 = "The Funding Organization was successfully deleted.",
     *     404 = "The requested Funding Organization was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(FundingOrganization::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Get the logo for a Funding Organization.
     *
     * @param integer $id The id of the Funding Organization to get the logo for.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   statusCodes = {
     *     200 = "Returned when successful.",
     *     404 = "Returned when the Funding Organization is not found or it does not have a logo."
     *   }
     * )
     *
     * @Rest\Get("/{id}/logo")
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction($id)
    {
        return $this->getProperty(FundingOrganization::class, $id, 'logo');
    }

    /**
     * Set or replace the logo of a Funding Organization via multipart/form-data POST.
     *
     * @param integer $id      The id of the Funding Organization to replace the logo for.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   parameters = {
     *     {"name"="logo", "dataType"="file", "required"="true"}
     *   },
     *   statusCodes = {
     *     204 = "Returned when the logo is successfully set or replaced.",
     *     404 = "Returned when the Funding Organization is not found."
     *   }
     * )
     *
     * @Rest\Post("/{id}/logo")
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction($id, Request $request)
    {
        return $this->postProperty(FundingOrganization::class, $id, 'logo', $request);
    }

    /**
     * Set or replace the logo of a Funding Organization via HTTP PUT file upload.
     *
     * @param integer $id      The id of the Funding Organization to replace the logo for.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   statusCodes = {
     *     204 = "Returned when the logo is successfully set or replaced.",
     *     404 = "Returned when the Funding Organization is not found."
     *   }
     * )
     *
     * @Rest\Put("/{id}/logo")
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction($id, Request $request)
    {
        return $this->putProperty(FundingOrganization::class, $id, 'logo', $request);
    }
}
