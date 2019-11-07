<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Util\UrlValidation;
use App\Entity\DatasetSubmission;
use App\Form\DatasetSubmissionType;

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
     * @Route(
     *     "/api/dataset_submission",
     *     name="pelagos_api_dataset_submission_get_collection",
     *     methods={"GET"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
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
     * @Route(
     *     "/api/dataset_submission/{id}",
     *     name="pelagos_api_dataset_submission_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return DatasetSubmission
     */
    public function getAction(int $id)
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
     * @Route(
     *     "/api/dataset_submission",
     *     name="pelagos_api_dataset_submission_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
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
     * @throws BadRequestHttpException When the submission has already been submitted.
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
     * @Route(
     *     "/api/dataset_submission/{id}",
     *     name="pelagos_api_dataset_submission_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);
        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            throw new BadRequestHttpException('This submission has already been submitted');
        }
        $this->handleUpdate(DatasetSubmissionType::class, DatasetSubmission::class, $id, $request, 'PUT');
        foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
            $this->entityHandler->update($datasetContact);
        }
        foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
            $this->entityHandler->update($metadataContact);
        }
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Dataset Submission with the submitted data.
     *
     * @param integer $id      The id of the Dataset Submission to update.
     * @param Request $request The request object.
     *
     * @throws BadRequestHttpException When the submission has already been submitted.
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
     * @Route(
     *     "/api/dataset_submission/{id}",
     *     name="pelagos_api_dataset_submission_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);
        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            throw new BadRequestHttpException('This submission has already been submitted');
        }
        $datasetSubmission = $this->handleUpdate(DatasetSubmissionType::class, DatasetSubmission::class, $id, $request, 'PATCH');
        foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
            $this->entityHandler->update($datasetContact);
        }
        foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
            $this->entityHandler->update($metadataContact);
        }
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
     * @Route(
     *     "/api/dataset_submission/{id}",
     *     name="pelagos_api_dataset_submission_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(DatasetSubmission::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Return a list of files uploaded for a dataset submission.
     *
     * @param integer $id The id of the dataset submission.
     *
     * @Route(
     *     "/api/dataset_submission/uploaded-files/{id}",
     *     name="pelagos_api_dataset_submission_get_uploaded_files",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return array The list of uploaded files.
     */
    public function getUploadedFilesAction(int $id)
    {
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);
        // If the dataset transfer type is not upload.
        if ($datasetSubmission->getDatasetFileTransferType() !== DatasetSubmission::TRANSFER_TYPE_UPLOAD) {
            // Return empty file list.
            return array();
        }
        $datasetFileUri = $datasetSubmission->getDatasetFileUri();
        // If the datasetFileUri is not set.
        if (empty($datasetFileUri)) {
            // Return empty file list.
            return array();
        }
        // Initialize file info.
        $fileInfo = array(
            'name' => basename($datasetFileUri),
            'size' => -1,
            'uuid' => '00000000-0000-0000-0000-000000000000',
        );
        // Try to get file info from the file.
        try {
            $file = new \SplFileInfo($datasetFileUri);
            $fileInfo['name'] = $file->getFilename();
            $fileInfo['size'] = $file->getSize();
        } catch (\Exception $e) {
            // Just use defaults if we're unable to get file info (e.g. file has been deleted from disk).
        }
        // Match the UUID out of the datsetFileUri.
        if (preg_match('!/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/!', $datasetFileUri, $matches)) {
            $uuid = $matches[1];
        }
        return array($fileInfo);
    }

    /**
     * Validate the url of the attribute.
     *
     * @param integer       $id            The id of the dataset submission.
     * @param Request       $request       The request object.
     * @param UrlValidation $urlValidation The URL validator.
     *
     * @Route(
     *     "/api/dataset_submission/validate-url/{id}",
     *     name="pelagos_api_dataset_submission_validate_url",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string
     */
    public function validateUrlAction(int $id, Request $request, UrlValidation $urlValidation)
    {
        $erddapUrl = $request->get('erddapUrl');

        if (empty($erddapUrl)) {
            return true;
        }

        return $urlValidation->validateUrl($erddapUrl);
    }
}
