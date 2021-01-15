<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Util\Datastore;
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
}
