<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Entity\DataRepository;
use App\Form\DataRepositoryType;

/**
 * The DataRepository api controller.
 */
class DataRepositoryController extends EntityController
{
    /**
     * Get a count of Data Repositories.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Data Repositories",
     *       "data_class": "Pelagos\Entity\DataRepository"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Data Repositories was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories/count",
     *     name="pelagos_api_data_repositories_count",
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
        return $this->handleCount(DataRepository::class, $request);
    }

    /**
     * Validate a value for a property of a Data Repository.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
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
     *     "/api/data-repositories/validateProperty",
     *     name="pelagos_api_data_repositories_validate_property",
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
        return $this->validateProperty(DataRepositoryType::class, DataRepository::class, $request);
    }

    /**
     * Validate a value for a property of an existing Data Repository.
     *
     * @param integer $id      The id of the existing Data Repository.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Data Repository was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories/{id}/validateProperty",
     *     name="pelagos_api_data_repositories_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction($id, Request $request)
    {
        return $this->validateProperty(DataRepositoryType::class, DataRepository::class, $request, $id);
    }

    /**
     * Get a collection of Data Repositories.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Data Repositories",
     *       "data_class": "Pelagos\Entity\DataRepository"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\DataRepository>",
     *   statusCodes = {
     *     200 = "The requested collection of Data Repositories was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories",
     *     name="pelagos_api_data_repositories_get_collection",
     *     methods={"GET"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DataRepository::class, $request);
    }

    /**
     * Get a single Data Repository for a given id.
     *
     * @param integer $id The id of the Data Repository to return.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   output = "Pelagos\Entity\DataRepository",
     *   statusCodes = {
     *     200 = "The requested Data Repository was successfully retrieved.",
     *     404 = "The requested Data Repository was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories/{id}",
     *     name="pelagos_api_data_repositories_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return DataRepository The Data Repository that was retrieved.
     */
    public function getAction($id)
    {
        return $this->handleGetOne(DataRepository::class, $id);
    }

    /**
     * Replace a Data Repository with the submitted data.
     *
     * @param integer $id      The id of the Data Repository to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataRepositoryType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Data Repository was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Data Repository.",
     *     404 = "The requested Data Repository was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories/{id}",
     *     name="pelagos_api_data_repositories_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(DataRepositoryType::class, DataRepository::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Data Repository with the submitted data.
     *
     * @param integer $id      The id of the Data Repository to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Repositories",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataRepositoryType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Data Repository was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Data Repository.",
     *     404 = "The requested Data Repository was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-repositories/{id}",
     *     name="pelagos_api_data_repositories_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DataRepositoryType::class, DataRepository::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }
}
