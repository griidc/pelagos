<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Entity\FundingOrganization;
use App\Form\FundingOrganizationType;

/**
 * The FundingOrganization api controller.
 */
class FundingOrganizationController extends EntityController
{
    /**
     * Get a count of Funding Organizations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Funding Organizations",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Funding Organizations",
     *       "data_class": "Pelagos\Entity\FundingOrganization"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Funding Organizations was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/funding-organizations/count",
     *     name="pelagos_api_funding_organizations_count",
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
        return $this->handleCount(FundingOrganization::class, $request);
    }

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
     * @Route(
     *     "/api/funding-organizations/validateProperty",
     *     name="pelagos_api_funding_organizations_validate_property",
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
     * @Route(
     *     "/api/funding-organizations/{id}/validateProperty",
     *     name="pelagos_api_funding_organizations_validate_property_existing",
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
     * @Route(
     *     "/api/funding-organizations",
     *     name="pelagos_api_funding_organizations_get_collection",
     *     methods={"GET"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
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
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return FundingOrganization The Funding Organization that was retrieved.
     */
    public function getAction(int $id)
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
     * @Route(
     *     "/api/funding-organizations",
     *     name="pelagos_api_funding_organizations_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
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
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
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
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
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
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
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
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_get_logo",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction(int $id)
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
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_post_logo",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction(int $id, Request $request)
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
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_put_logo",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction(int $id, Request $request)
    {
        return $this->putProperty(FundingOrganization::class, $id, 'logo', $request);
    }
}
