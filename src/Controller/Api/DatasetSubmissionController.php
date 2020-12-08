<?php

namespace App\Controller\Api;

use App\Util\FolderStructureGenerator;
use Doctrine\ORM\EntityManagerInterface;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;

use Swagger\Annotations as SWG;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\File;
use App\Entity\DatasetSubmission;
use App\Entity\Fileset;

use App\Util\UrlValidation;

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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Get a collection of Dataset Submissions.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Dataset Submissions was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Get a single Dataset Submission for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Dataset Submission was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Dataset Submission was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Create a new Dataset Submission from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Dataset Submission was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Dataset Submission."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Replace a Dataset Submission with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Dataset Submission was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Dataset Submission."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Dataset Submission was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Update a Dataset Submission with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Dataset Submission was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Dataset Submission."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Dataset Submission was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
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
     * @Operation(
     *     tags={"Dataset Submission"},
     *     summary="Delete a Dataset Submission.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Dataset Submission was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="You do not have sufficient privileges to delete this Dataset Submission."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Dataset Submission was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $fileData = array();
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);
        $pathInfo = ($request->get('path')) ? $request->get('path') : '';
        if ($datasetSubmission->getFileset() instanceof Fileset) {
            $fileData = $folderStructureGenerator->getFolderJson($datasetSubmission->getFileset()->getId(), $pathInfo);
        }
        return $this->makeJsonResponse($fileData);
    }

    /**
     * Adds a file to a dataset submission.
     *
     * @param DatasetSubmission      $datasetSubmission The id of the dataset submission.
     * @param Request                $request           The request body sent with file metadata.
     * @param EntityManagerInterface $entityManager     Entity manager interface to doctrine operations.
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_add_file_dataset_submission",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return Response
     */
    public function addFile(DatasetSubmission $datasetSubmission, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $fileset = $datasetSubmission->getFileset();
        if ($fileset instanceof Fileset) {
            if ($fileset->doesFileExist($request->get('name'))) {
                $existingFile = $fileset->getExistingFile($request->get('name'));
                $existingFile->setStatus(FILE::FILE_DELETED);
            }
        } else {
            $fileset = new Fileset();
            $datasetSubmission->setFileset($fileset);
        }
        $newFile = new File();
        $newFile->setFilePathName(trim($request->get('name')));
        $newFile->setFileSize($request->get('size'));
        $newFile->setUploadedAt(new \DateTime('now'));
        $newFile->setUploadedBy($this->getUser()->getPerson());
        $newFile->setPhysicalFilePath($request->get('path'));
        $fileset->addFile($newFile);
        $entityManager->persist($newFile);
        $entityManager->flush();
        return $this->makeNoContentResponse();
    }

    /**
     * Checks if a File already exits with the same name.
     *
     * @param DatasetSubmission $datasetSubmission The id of the dataset submission.
     * @param Request           $request           The request body sent with file metadata.
     *
     * @Route(
     *     "/api/files_dataset_submission/file-exists/{id}",
     *     name="pelagos_api_check_file_exists_dataset_submission",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return boolean|string
     */
    public function checkFileExists(DatasetSubmission $datasetSubmission, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $fileset = $datasetSubmission->getFileset();
        $newFileName = $request->get('name');
        if (!$fileset instanceof Fileset) {
            return false;
        }
        return $fileset->doesFileExist($newFileName);
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
