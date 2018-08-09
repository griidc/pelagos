<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Bundle\AppBundle\Form\NationalDataCenterType;

use Pelagos\Entity\NationalDataCenter;
use Pelagos\Entity\Entity;

/**
 * The API Controller Class for NationalDataCenter.
 */
class NationalDataCenterController extends EntityController
{
    /**
     * Get a count of National Data Centers.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "National Data Centers",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "National Data Centers",
     *       "data_class": "Pelagos\Entity\NationalDataCenter"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of National Data Centers was successfully returned.",
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
        return $this->handleCount(NationalDataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a National Data center.
     *
     * @param Request $request A Symfony request instance.
     *
     * @ApiDoc(
     *     section = "National Data Center",
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
        return $this->validateProperty(NationalDataCenterType::class, NationalDataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a existing National Data center.
     *
     * @param integer $id      The id of the existing National Data center.
     * @param Request $request A Symfony request instance.
     *
     * @ApiDoc(
     *     section = "National Data Center",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested National Data center was not found.",
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
        return $this->validateProperty(NationalDataCenterType::class, NationalDataCenter::class, $request, $id);
    }

    /**
     * Get a collection of National Datacenters.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "National Data Center",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "National Data Center",
     *       "data_class": "Pelagos\Entity\NationalDataCenter"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\NationalDataCenter>",
     *   statusCodes = {
     *     200 = "The requested collection of National Data Centers was successfully retrieved.",
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
        return $this->handleGetCollection(NationalDataCenter::class, $request);
    }

    /**
     * Get a National data center for a given id.
     *
     * @param string $id The id of the National Data center.
     *
     * @ApiDoc(
     *     section = "National Data Center",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *     statusCodes = {
     *       200 = "Successfully retrieved the National Data Center.",
     *       404 = "The requested National Data Center was not found.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Entity(NationalDataCenter)
     */
    public function getAction($id)
    {
        return $this->handleGetOne(NationalDataCenter::class, $id);
    }

    /**
     * Create a new National Data Center.
     *
     * @param Request $request The Symfony request object.
     *
     * @ApiDoc(
     *     section = "National Data Center",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *     statusCodes = {
     *       201 = "Successfully created a new National Data Center.",
     *       400 = "The request could not be processed due to validation or other errors.",
     *       403 = "The authenticated user was not authorized to create the National Data Center.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     *
     * @return Response A response object with empty body and status code.
     */
    public function postAction(Request $request)
    {
        $nationalDataCenter = $this->handlePost(NationalDataCenterType::class, NationalDataCenter::class, $request);
        return $this->makeCreatedResponse('pelagos_api_national_data_center_get', $nationalDataCenter->getId());
    }

    /**
     * Replace a National Data Centerwith the submitted data.
     *
     * @param integer $id      The id of the National Data Center to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "National Data Centers",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *   statusCodes = {
     *     204 = "The National Data Center was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the National Data Center.",
     *     404 = "The requested National Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(NationalDataCenterType::class, NationalDataCenter::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a National Data Center with the submitted data.
     *
     * @param integer $id      The id of the National Data Center to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "National Data Centers",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\NationalDataCenterType", "name" = ""},
     *   statusCodes = {
     *     204 = "The National Data Center was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the National Data Center.",
     *     404 = "The requested National Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(NationalDataCenterType::class, NationalDataCenter::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a National Data Center.
     *
     * @param integer $id The id of the National Data Center to delete.
     *
     * @ApiDoc(
     *   section = "National Data Centers",
     *   statusCodes = {
     *     204 = "The National Data Center was successfully deleted.",
     *     404 = "The requested National Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(NationalDataCenter::class, $id);
        return $this->makeNoContentResponse();
    }
}
