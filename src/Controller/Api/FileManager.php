<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Util\Datastore;
use App\Util\FileUploader;
use App\Util\RenameDuplicate;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @param RenameDuplicate        $renameDuplicate   The duplicate renaming utility.
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_add_file_dataset_submission",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @return Response
     */
    public function addFile(DatasetSubmission $datasetSubmission, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader, RenameDuplicate $renameDuplicate)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $fileMetadata = $fileUploader->combineChunks($request);

        $fileName = $fileMetadata['name'];
        $filePath = $fileMetadata['path'];
        $fileSize = $fileMetadata['size'];

        $fileset = $datasetSubmission->getFileset();

        if ($fileset instanceof Fileset) {
            while ($fileset->doesFileExist($fileName)) {
                $fileName = $renameDuplicate->renameFile($fileName);
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

        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Delete a file or folder.
     *
     * @param DatasetSubmission      $datasetSubmission The id of the dataset submission.
     * @param Request                $request           The request body
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     * @param Datastore              $datastore         Datastore to manipulate the file on disk.
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
    public function delete(DatasetSubmission $datasetSubmission, Request $request, EntityManagerInterface $entityManager, Datastore $datastore): Response
    {
        $deleteFilePath = $request->get('path');
        $isDir = $request->get('isDir');
        $fileset = $datasetSubmission->getFileset();
        if (!$deleteFilePath) {
            throw new BadRequestHttpException('Please provide a file path');
        }
        if ($fileset instanceof Fileset) {
            if ($isDir === 'true') {
                $files = $fileset->getFilesInDirectory($deleteFilePath);
                foreach ($files as $file) {
                    $this->deleteFile($file, $fileset, $datastore);
                }
            } else {
                $existingFile = $fileset->getExistingFile($deleteFilePath);
                $this->deleteFile($existingFile, $fileset, $datastore);
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
     * @param File      $file      File entity that needs to be deleted.
     * @param Fileset   $fileset   Fileset entity instance.
     * @param Datastore $datastore Datastore to manipulate the file on disk.
     *
     * @return void
     */
    private function deleteFile(File $file, Fileset $fileset, Datastore $datastore) : void
    {
        if ($file->getStatus() === File::FILE_NEW) {
            $deleteFile = unlink($file->getPhysicalFilePath());
            $deleteFolder = rmdir(dirname($file->getPhysicalFilePath()));
            if ($deleteFile and $deleteFolder) {
                $fileset->removeFile($file);
            } else {
                throw new BadRequestHttpException('Unable to delete file');
            }
        } elseif ($file->getStatus() === File::FILE_DONE) {
            $file->setStatus(File::FILE_DELETED);
            $filePath = $file->getPhysicalFilePath();
            $newFilePath = $filePath . Datastore::MARK_FILE_AS_DELETED;
            $newFilePath = $datastore->renameFile($filePath, $newFilePath, true);
            $file->setPhysicalFilePath($newFilePath);
        }
    }

    /**
     * Update a file entity.
     *
     * @param DatasetSubmission      $datasetSubmission The id of the dataset submission.
     * @param Request                $request           The request body
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     * @param Datastore              $datastore         Datastore to manipulate the file on disk.
     *
     * @Route("/api/file_update_filename/{id}", name="pelagos_api_file_update_filename", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="datasetSubmission")
     *
     * @throws BadRequestHttpException When the destination file name already exists.
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function updateFileOrFolderName(DatasetSubmission $datasetSubmission, Request $request, EntityManagerInterface $entityManager, Datastore $datastore) : Response
    {
        $newFileName = $request->get('newFileFolderPathDir');
        $existingFilePath = $request->get('path');
        $isDir = $request->get('isDir');
        $fileset = $datasetSubmission->getFileset();
        if ($fileset instanceof Fileset) {
            if ($isDir === true) {
                $files = $fileset->getFilesInDirectory($existingFilePath);
                foreach ($files as $file) {
                    $this->updateFileName($file, $fileset, $newFileName, $datastore);
                }
            } else {
                $existingFile = $fileset->getExistingFile($existingFilePath);
                $this->updateFileName($existingFile, $fileset, $newFileName, $datastore);
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
     * @param File      $file        File entity that needs to be renamed.
     * @param Fileset   $fileset     Fileset entity instance.
     * @param string    $newFileName New file name for the file.
     * @param Datastore $datastore   Datastore to manipulate the file on disk.
     *
     * @throws BadRequestHttpException When the destination file name already exists.
     *
     * @return void
     */
    private function updateFileName(File $file, Fileset $fileset, string $newFileName, Datastore $datastore) : void
    {
        if (!$fileset->doesFileExist($newFileName)) {
            // Rename file on disk if it is already processed
            if ($file->getStatus() === File::FILE_DONE) {
                $newPhysicalFilePath = $datastore->renameFile($file->getPhysicalFilePath(), $newFileName);
                $file->setPhysicalFilePath($newPhysicalFilePath);
            }
            $file->setFilePathName($newFileName);
        } else {
            throw new BadRequestHttpException('File with same name and folder already exists');
        }
    }
}
