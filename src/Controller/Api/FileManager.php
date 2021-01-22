<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FileManager API used by File Manager app.
 */
class FileManager extends AbstractFOSRestController
{
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

    /**
     * Download a file from disk.
     *
     * @param File                   $file          File entity instance.
     * @param Datastore              $datastore     Datastore to manipulate the file on disk.
     *
     * @Route("/api/file/download/{id}", name="pelagos_api_file_download", defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="file")
     *
     * @return Response
     */
    public function downloadFile(File $file, Datastore $datastore): Response
    {
        $response = new StreamedResponse(function () use ($file, $datastore) {
            $outputStream = fopen('php://output', 'wb');
            if ($file->getStatus() === File::FILE_DONE) {
                try {
                    $fileStream = $datastore->getFile($file->getPhysicalFilePath())['fileStream'];
                } catch (\Exception $exception) {
                    throw new BadRequestHttpException($exception->getMessage());
                }
            } else {
                $fileStream = fopen($file->getPhysicalFilePath(), 'r');
            }
            stream_copy_to_stream($fileStream, $outputStream);
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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="datasetSubmission")
     *
     * @return Response
     */
    public function downloadZipAllFiles(DatasetSubmission $datasetSubmission): Response
    {
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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="datasetSubmission")
     *
     * @return Response
     */
    public function doesZipFileExist(DatasetSubmission $datasetSubmission): Response
    {
        $zipFilePath = $this->getZipFilePath($datasetSubmission);
        $data = $zipFilePath ? true : false;
        return new Response(
            json_encode($data),
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
        if ($fileset instanceof Fileset and $fileset->isDone()) {
            $zipFilePath = $fileset->getZipFilePath();
        }
        return $zipFilePath;
    }
}
