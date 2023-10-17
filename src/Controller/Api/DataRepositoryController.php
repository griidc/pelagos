<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
     *
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
     *
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
     *
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
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(DataRepositoryType::class, DataRepository::class, $request, $id);
    }

    /**
     * Get a collection of Data Repositories.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/data-repositories",
     *     name="pelagos_api_data_repositories_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
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
     *
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
    public function getAction(int $id)
    {
        return $this->handleGetOne(DataRepository::class, $id);
    }

    /**
     * Replace a Data Repository with the submitted data.
     *
     * @param integer $id      The id of the Data Repository to replace.
     * @param Request $request The request object.
     *
     *
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
    public function putAction(int $id, Request $request)
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
     *
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
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(DataRepositoryType::class, DataRepository::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }
}
