<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\DatasetSubmission;
use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

/**
 * The Dataset Submission api controller.
 */
class DatasetSubmissionController extends EntityController
{
    /**
     * Get a collection of Dataset Submissions.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Dataset Submission",
     *       "data_class": "Pelagos\Entity\DatasetSubmission"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\DatasetSubmission>",
     *   statusCodes = {
     *     200 = "The requested collection of Dataset Submissions was successfully retrieved.",
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
        return $this->handleGetCollection(DatasetSubmission::class, $request);
    }

    /**
     * Get a single Dataset Submission for a given id.
     *
     * @param integer $id The id of the Dataset Submission to return.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   output = "Pelagos\Entity\DatasetSubmission",
     *   statusCodes = {
     *     200 = "The requested Dataset Submission was successfully retrieved.",
     *     404 = "The requested Dataset Submission was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return DatasetSubmission
     */
    public function getAction($id)
    {
        return $this->handleGetOne(DatasetSubmission::class, $id);
    }

    /**
     * Create a new Dataset Submission from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType", "name" = ""},
     *   statusCodes = {
     *     201 = "The Dataset Submission was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the Dataset Submission.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Dataset Submission in the Location header.
     */
    public function postAction(Request $request)
    {
        $datasetSubmission = $this->handlePost(DatasetSubmissionType::class, DatasetSubmission::class, $request);
        return $this->makeCreatedResponse('pelagos_api_dataset_submission_get', $datasetSubmission->getId());
    }

    /**
     * Replace a Dataset Submission with the submitted data.
     *
     * @param integer $id      The id of the Dataset Submission to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Dataset Submission was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Dataset Submission.",
     *     404 = "The requested Dataset Submission was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(DatasetSubmissionType::class, DatasetSubmission::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Dataset Submission with the submitted data.
     *
     * @param integer $id      The id of the Dataset Submission to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Dataset Submission was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Dataset Submission.",
     *     404 = "The requested Dataset Submission was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DatasetSubmissionType::class, DatasetSubmission::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Dataset Submission.
     *
     * @param integer $id The id of the Dataset Submission to delete.
     *
     * @ApiDoc(
     *   section = "Dataset Submission",
     *   statusCodes = {
     *     204 = "The Dataset Submission was successfully deleted.",
     *     403 = "You do not have sufficient privileges to delete this Dataset Submission.",
     *     404 = "The requested Dataset Submission was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction($id)
    {
        $this->handleDelete(DatasetSubmission::class, $id);
        return $this->makeNoContentResponse();
    }
}
