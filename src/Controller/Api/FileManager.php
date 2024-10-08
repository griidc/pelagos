<?php

namespace App\Controller\Api;

use App\Entity\Account;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Event\LogActionItemEventDispatcher;
use App\Message\RenameFile;
use App\Security\Voter\PelagosEntityVoter;
use App\Util\Datastore;
use App\Util\FileNameUtilities;
use App\Util\FileUploader;
use App\Util\FolderStructureGenerator;
use App\Util\ZipFiles;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

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
     *
     *
     * @throws BadRequestHttpException When the file renamer fails because the sequence is over 999.
     * @return Response
     */
    #[Route(path: '/api/files_dataset_submission/{id}', name: 'pelagos_api_add_file_dataset_submission', methods: ['POST'], defaults: ['_format' => 'json'], requirements: ['id' => '\d+'])]
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
     *
     * @return Response
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[IsGranted(PelagosEntityVoter::CAN_DELETE, subject: 'datasetSubmission')]
    #[Route(path: '/api/file_delete/{id}', name: 'pelagos_api_file_delete', methods: ['DELETE'], defaults: ['_format' => 'json'], requirements: ['id' => '\d+'])]
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
     *
     *
     * @throws BadRequestHttpException When the destination file or folder name already exists.
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[IsGranted(PelagosEntityVoter::CAN_EDIT, subject: 'datasetSubmission')]
    #[Route(path: '/api/file_update_filename/{id}', name: 'pelagos_api_file_update_filename', methods: ['PUT'], defaults: ['_format' => 'json'])]
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
     *
     * @throws AccessDeniedHttpException Error thrown when file not available for download.
     * @throws BadRequestHttpException Error thrown when file stream cannot be opened.
     * @return Response
     */
    #[Route(path: '/api/file/download/{id}', name: 'pelagos_api_file_download', defaults: ['_format' => 'json'])]
    public function downloadFile(File $file, Datastore $datastore, Request $request, LogActionItemEventDispatcher $logActionItemEventDispatcher, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession()->getId() ?? '';

        if (
            $file->getFileset()->getDatasetSubmission()->getDataset()->getAvailabilityStatus() !==
            DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
            and
            !$this->isGranted('CAN_EDIT', $file)
        ) {
            throw new AccessDeniedHttpException('File unavailable for download');
        }

        $udi = $file->getFileset()->getDatasetSubmission()->getDataset()->getUdi();

        if ($request->headers->get('referer') and preg_match("/^.*\/data\/$udi$/", $request->headers->get('referer'))) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Account) {
                $type = 'GoMRI';
                $typeId = $currentUser->getUserId();
            } else {
                $type = 'Non-GoMRI';
                $typeId = 'anonymous';
            }

            $logActionItemEventDispatcher->dispatch(
                array(
                'actionName' => 'Single File Download',
                'subjectEntityName' => 'Pelagos\Entity\Dataset',
                'subjectEntityId' => $file->getFileset()->getDatasetSubmission()->getDataset()->getId(),
                'payLoad' => array('userType' => $type, 'userId' => $typeId, 'filename' => $file->getFilePathName(), 'udi' => $file->getFileset()->getDatasetSubmission()->getDataset()->getUdi(), 'sessionId' => $session),
                ),
                'single_file_download'
            );
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
     */
    #[Route(path: '/api/file_zip_download_all/{id}', name: 'pelagos_api_file_zip_download_all', defaults: ['_format' => 'json'])]
    public function downloadZipAllFiles(
        DatasetSubmission $datasetSubmission,
        LogActionItemEventDispatcher $logActionItemEventDispatcher,
        ZipFiles $zipFiles,
        Request $request,
        Datastore $datastore,
        RequestStack $requestStack,
    ): Response {
        $dataset = $datasetSubmission->getDataset();
        $udi = $dataset->getUdi();
        // Only log if this is downloaded from dataland. We don't log review downloads.
        $referer = $request->headers->get('referer');
        if (!empty($referer) and preg_match("/^.*\/data\/$udi$/", $referer)) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Account) {
                $type = 'GoMRI';
                $typeId = $currentUser->getUserId();
            } else {
                $type = 'Non-GoMRI';
                $typeId = 'anonymous';
            }

            $session = $requestStack->getSession()->getId();
            $logActionItemEventDispatcher->dispatch(
                array(
                    'actionName' => 'File Download',
                    'subjectEntityName' => 'Pelagos\Entity\Dataset',
                    'subjectEntityId' => $dataset->getId(),
                    'payLoad' => array('userType' => $type, 'userId' => $typeId, 'udi' => $dataset->getUdi(), 'sessionId' => $session),
                ),
                'file_download'
            );
        }

        $zipFileName = str_replace(':', '.', $dataset->getUdi()) . '.zip';

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $zipFileName,
        );

        $headers = array(
            'Content-Disposition' => $disposition,
            'Content-type' => 'application/zip',
        );

        return new StreamedResponse(function () use ($datasetSubmission, $zipFiles, $datastore, $zipFileName) {
            $outputStream = GuzzlePsr7Utils::streamFor(fopen('php://output', 'wb'));
            $zipFiles->start($outputStream, $zipFileName);

            $fileset = $datasetSubmission->getFileset();

            foreach ($fileset->getProcessedFiles() as $file) {
                $filePathName = $file->getFilePathName();
                $fileStream = $datastore->getFile($file->getPhysicalFilePath());
                $zipFiles->addFile($filePathName, $fileStream);
            }

            $zipFiles->finish();
        }, 200, $headers);
    }

    /**
     * Checks if the zip file exists for the dataset.
     */
    #[Route(path: '/api/check_zip_exists/{id}', name: 'pelagos_api_check_zip_exists', defaults: ['_format' => 'json'])]
    public function doesZipFileExist(DatasetSubmission $datasetSubmission): Response
    {
        return new Response(
            json_encode($datasetSubmission?->getFileset()?->getProcessedFiles()?->count() > 0),
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/json',
            )
        );
    }
}
