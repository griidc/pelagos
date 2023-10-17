<?php

namespace App\Controller\Api;

use App\Util\FileUploader;
use App\Util\FolderStructureGenerator;
use App\Util\IngestUtil;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Account;
use App\Entity\File;
use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use App\Util\UrlValidation;
use App\Form\DatasetSubmissionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
     *
     *
     * @Route(
     *     "/api/dataset_submission",
     *     name="pelagos_api_dataset_submission_get_collection",
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
        return $this->handleGetCollection(DatasetSubmission::class, $request);
    }

    /**
     * Get a single Dataset Submission for a given id.
     *
     * @param integer $id The id of the Dataset Submission to return.
     *
     *
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
     *
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
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
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
     *
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
     *
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
     * @param integer                   $id                       The id of the dataset submission.
     * @param Request                   $request                  The request object.
     * @param FolderStructureGenerator  $folderStructureGenerator Folder structure generator Util class.
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_get_files_dataset_submission",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return Response The list of uploaded files.
     */
    public function getFiles(int $id, Request $request, FolderStructureGenerator $folderStructureGenerator): Response
    {
        $fileData = array();
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);
        $pathInfo = ($request->get('path')) ? $request->get('path') : '';
        if ($datasetSubmission->getFileset() instanceof Fileset) {
            $fileData = $folderStructureGenerator->getFolderJson($datasetSubmission->getFileset()->getId(), $pathInfo);
        }
        return $this->makeJsonResponse($fileData);
    }

    /**
     * Returns a single file with a path.
     *
     * @param DatasetSubmission $datasetSubmission The id of the dataset submission.
     * @param Request           $request           The request object.
     *
     * @Route(
     *     "/api/file_dataset_submission/{id}",
     *     name="pelagos_api_get_file_dataset_submission",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return Response
     */
    public function getFile(DatasetSubmission $datasetSubmission, Request $request): Response
    {
        $fileset = $datasetSubmission->getFileset();
        $path = $request->get('path');
        if (!$path) {
            throw new BadRequestHttpException('Please provide a file path');
        }
        if ($fileset instanceof Fileset) {
            $existingFile = $fileset->getExistingFile($path);
            if ($existingFile instanceof File) {
                return $this->makeJsonResponse(['id' => $existingFile->getId()]);
            } else {
                throw new BadRequestHttpException('No such file found');
            }
        } else {
            throw new BadRequestHttpException('No files exist in this Dataset');
        }
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

    /**
     * Returns a list of global ingest folders.
     *
     * @Route(
     *     "/api/dataset_submission_folder_list",
     *     name="pelagos_api_get_folder_list_dataset_submission",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     )
     *
     * @View()
     *
     * @return Response
     */
    public function getGlobalIngestFolders(IngestUtil $ingestUtil): Response
    {
        if (!($this->getUser() instanceof Account)) {
            throw new AccessDeniedException('Must be logged in');
        }
        return $this->makeJsonResponse($ingestUtil->getUsersIngestFoldersInIncomingDir($this->getUser()->getUserID()));
    }
}
