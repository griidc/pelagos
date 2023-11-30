<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Message\RenameFile;
use App\Util\Datastore;
use App\Util\FileNameUtilities;
use App\Util\FileUploader;
use App\Util\FolderStructureGenerator;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FileManager API used by File Manager app.
 */
class FileManager extends AbstractFOSRestController
{
    /**
     * Adds a file to a dataset submission.
     *
     * @param DatasetSubmission      $datasetSubmission The id of the dataset submission.
     * @param Request                $request           The request body sent with file metadata.
     * @param EntityManagerInterface $entityManager     Entity manager interface to doctrine operations.
     * @param FileUploader           $fileUploader      File upload handler service.
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_add_file_dataset_submission",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     *
     * @throws BadRequestHttpException When the file renamer fails because the sequence is over 999.
     *
     * @return Response
     */
    public function addFile(DatasetSubmission $datasetSubmission, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $fileMetadata = $fileUploader->combineChunks($request);
        } catch (\Exception $exception) {
            return new JsonResponse(['code' => 400, 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $fileName = $fileMetadata['name'];
        $filePath = $fileMetadata['path'];
        $fileSize = $fileMetadata['size'];

        $fileset = $datasetSubmission->getFileset();

        $isRenamed = false;

        if ($fileset instanceof Fileset) {
            while ($fileset->doesFileExist($fileName)) {
                try {
                    $fileName = FileNameUtilities::renameFile($fileName);
                } catch (\Exception $e) {
                    throw new BadRequestHttpException($e->getMessage());
                }
                $isRenamed = true;
            }
        } else {
            $fileset = new Fileset();
            $datasetSubmission->setFileset($fileset);
        }
        $newFile = new File();
        $newFile->setFilePathName(trim($fileName));
        $newFile->setFileSize($fileSize);
        $newFile->setUploadedAt(new \DateTime('now'));
        $newFile->setUploadedBy($this->getUser()->getPerson());
        $newFile->setPhysicalFilePath($filePath);
        $fileset->addFile($newFile);
        $entityManager->persist($newFile);
        $entityManager->flush();

        return new JsonResponse(
            [
                'fileName' => $fileName,
                'isRenamed' => $isRenamed
            ]
        );
    }

    /**
     * Delete a file or folder.
     *
     * @param DatasetSubmission      $datasetSubmission The id of the dataset submission.
     * @param Request                $request           The request body
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     * @param MessageBusInterface    $messageBus        Message bus interface.
     *
     * @Route(
     *     "/api/file_delete/{id}",
     *     name="pelagos_api_file_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_DELETE", subject="datasetSubmission")
     *
     * @return Response
     */
    public function delete(
        DatasetSubmission $datasetSubmission,
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ): Response {
        $deleteFilePath = $request->get('path');
        $deleteFileId = $request->get('fileId');
        $isDir = $request->get('isDir');
        $fileset = $datasetSubmission->getFileset();
        if ((!$deleteFilePath and $isDir === true) or !$deleteFileId) {
            throw new BadRequestHttpException('Please provide a file path/id');
        }
        if ($fileset instanceof Fileset) {
            if ($isDir === 'true') {
                $files = $fileset->getFilesInDirectory($deleteFilePath);
                foreach ($files as $file) {
                    $this->deleteFile($file, $messageBus);
                }
            } else {
                $existingFile = $fileset->getFileById($deleteFileId);
                $this->deleteFile($existingFile, $messageBus);
            }
            $entityManager->flush();
        } else {
            throw new BadRequestHttpException('No files exist in this Dataset');
        }
        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Delete individual file from disk or mark as deleted.
     *
     * @param File                $file       File entity that needs to be deleted.
     * @param MessageBusInterface $messageBus Message bus interface.
     *
     * @return void
     */
    private function deleteFile(File $file, MessageBusInterface $messageBus): void
    {
        if ($file->getStatus() === File::FILE_NEW) {
            $deleteFile = unlink($file->getPhysicalFilePath());
            $deleteFolder = rmdir(dirname($file->getPhysicalFilePath()));
            if ($deleteFile and $deleteFolder) {
                $file->getFileset()->removeFile($file);
            } else {
                throw new BadRequestHttpException('Unable to delete file');
            }
        } elseif ($file->getStatus() === File::FILE_DONE) {
            $file->setStatus(File::FILE_DELETED);
            $renameMessage = new RenameFile($file->getId());
            $messageBus->dispatch($renameMessage);
        }
    }

    /**
     * Update a file entity.
     *
     * @param DatasetSubmission        $datasetSubmission        The id of the dataset submission.
     * @param Request                  $request                  The request body.
     * @param EntityManagerInterface   $entityManager            Entity manager interface instance.
     * @param FolderStructureGenerator $folderStructureGenerator Folder structure generator Util class.
     * @param MessageBusInterface      $messageBus               Message bus interface.
     *
     * @Route("/api/file_update_filename/{id}", name="pelagos_api_file_update_filename", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="datasetSubmission")
     *
     * @throws BadRequestHttpException When the destination file or folder name already exists.
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function updateFileOrFolderName(
        DatasetSubmission $datasetSubmission,
        Request $request,
        EntityManagerInterface $entityManager,
        FolderStructureGenerator $folderStructureGenerator,
        MessageBusInterface $messageBus
    ): Response {
        $newFileName = $request->get('newFileFolderPathDir');
        $existingFilePath = $request->get('path');
        $isDir = $request->get('isDir');
        $fileset = $datasetSubmission->getFileset();
        if ($fileset instanceof Fileset) {
            if ($isDir === true) {
                $existingFileDir = dirname($existingFilePath);
                if ($existingFileDir === '.') {
                    $existingFileDir = '';
                }
                $folderArray = $folderStructureGenerator->getFolderJson($datasetSubmission->getFileset()->getId(), $existingFileDir);
                $newFolderName = basename($newFileName);
                foreach ($folderArray as $folder) {
                    if ($folder['name'] === $newFolderName) {
                        throw new BadRequestHttpException('Folder already exists');
                    }
                }
                $files = $fileset->getFilesInDirectory($existingFilePath);
                foreach ($files as $file) {
                    $newFilePathName = implode("/", array_merge([$newFileName], $file->getFilePathParts($existingFilePath)));
                    $this->updateFileName($file, $newFilePathName, $messageBus);
                }
            } else {
                $existingFile = $fileset->getExistingFile($existingFilePath);
                $this->updateFileName($existingFile, $newFileName, $messageBus);
            }
            $entityManager->flush();
        } else {
            throw new BadRequestHttpException('No files exist in this Dataset');
        }

        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Update file name for single file entity.
     *
     * @param File                $file        File entity that needs to be renamed.
     * @param string              $newFileName New file name for the file.
     * @param MessageBusInterface $messageBus  Message bus interface.
     *
     * @throws BadRequestHttpException When the destination file name already exists.
     *
     * @return void
     */
    private function updateFileName(File $file, string $newFileName, MessageBusInterface $messageBus): void
    {
        if (!$file->getFileset()->doesFileExist($newFileName)) {
            $file->setFilePathName($newFileName);
            // Rename file on disk if it is already processed
            if ($file->getStatus() === File::FILE_DONE) {
                $renameFile = new RenameFile($file->getId());
                $messageBus->dispatch($renameFile);
            }
        } else {
            throw new BadRequestHttpException('File with same name and folder already exists');
        }
    }

    /**
     * Download a file from disk.
     *
     * @param File                   $file          File entity instance.
     * @param Datastore              $datastore     Datastore to manipulate the file on disk.
     *
     * @Route("/api/file/download/{id}", name="pelagos_api_file_download", defaults={"_format"="json"})
     *
     * @throws BadRequestHttpException Error thrown when file stream cannot be opened.
     * @throws AccessDeniedHttpException Error thrown when file not available for download.
     *
     * @return Response
     */
    public function downloadFile(File $file, Datastore $datastore): Response
    {
        if (
            $file->getFileset()->getDatasetSubmission()->getDataset()->getAvailabilityStatus() !==
            DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
            and
            !$this->isGranted('CAN_EDIT', $file)
        ) {
            throw new AccessDeniedHttpException('File unavailable for download');
        }
        $response = new StreamedResponse(function () use ($file, $datastore) {
            $outputStream = GuzzlePsr7Utils::streamFor(fopen('php://output', 'wb'));

            if ($file->getStatus() === File::FILE_DONE) {
                try {
                    $fileStream = $datastore->getFile($file->getPhysicalFilePath());
                } catch (\Exception $exception) {
                    throw new BadRequestHttpException($exception->getMessage());
                }
            } else {
                $resource = GuzzlePsr7Utils::tryFopen($file->getPhysicalFilePath(), 'r');
                $fileStream = GuzzlePsr7Utils::streamFor($resource);
            }
            GuzzlePsr7Utils::copyToStream($fileStream, $outputStream);
        });
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($file->getFilePathName())
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Download zip for all files in a dataset.
     *
     * @param DatasetSubmission $datasetSubmission The id of the dataset submission.
     *
     * @Route("/api/file_zip_download_all/{id}", name="pelagos_api_file_zip_download_all", defaults={"_format"="json"})
     *
     * @throws BadRequestHttpException When no zip file is found.
     * @throws AccessDeniedHttpException Error thrown when file not available for download.
     *
     * @return Response
     */
    public function downloadZipAllFiles(DatasetSubmission $datasetSubmission): Response
    {
        if (
            $datasetSubmission->getDataset()->getAvailabilityStatus() !==
            DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
            and
            !$this->isGranted('CAN_EDIT', $datasetSubmission)
        ) {
            throw new AccessDeniedHttpException('File unavailable for download');
        }

        $zipFilePath = $this->getZipFilePath($datasetSubmission);
        if ($zipFilePath) {
            $response = new StreamedResponse(function () use ($zipFilePath) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = fopen($zipFilePath, 'r');
                stream_copy_to_stream($fileStream, $outputStream);
            });
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                basename($zipFilePath)
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        } else {
            throw new BadRequestHttpException('No Zip file found');
        }
    }

    /**
     * Checks if the zip file exists for the dataset.
     *
     * @param DatasetSubmission $datasetSubmission The id of the dataset submission.
     *
     * @Route("/api/check_zip_exists/{id}", name="pelagos_api_check_zip_exists", defaults={"_format"="json"})
     *
     * @return Response
     */
    public function doesZipFileExist(DatasetSubmission $datasetSubmission): Response
    {
        $zipFilePath = $this->getZipFilePath($datasetSubmission);
        return new Response(
            json_encode($zipFilePath ? true : false),
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/json',
            )
        );
    }

    /**
     * Get the zip file path for the dataset.
     *
     * @param DatasetSubmission $datasetSubmission The id of the dataset submission.
     *
     * @return string
     */
    private function getZipFilePath(DatasetSubmission $datasetSubmission): string
    {
        $fileset = $datasetSubmission->getFileset();
        $zipFilePath = '';
        if ($fileset instanceof Fileset and $fileset->isDone() and $fileset->doesZipFileExist()) {
            $zipFilePath = $fileset->getZipFilePath();
        }
        return $zipFilePath;
    }
}
