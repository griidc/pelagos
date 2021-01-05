<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD API for File Entity.
 */
class FileController extends AbstractFOSRestController
{
    /**
     * Delete a file.
     *
     * @param File                   $file          File entity instance.
     * @param MessageBusInterface    $messageBus    Symfony messenger bus interface instance.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     * @param Datastore              $datastore     Datastore to manipulate the file on disk.
     *
     * @Route("/api/file/{id}", name="pelagos_api_file_delete", methods={"DELETE"}, defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_DELETE", subject="file")
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteFile(File $file, MessageBusInterface $messageBus, EntityManagerInterface $entityManager, Datastore $datastore)
    {
        $fileset = $file->getFileset();
        $fileset->removeFile($file);
        $filePath = $file->getPhysicalFilePath();
        if ($file->getStatus() === File::FILE_NEW) {
            $deleteFileMessage = new DeleteFile($filePath);
            $messageBus->dispatch($deleteFileMessage);
        } elseif ($file->getStatus() === File::FILE_DONE) {
            $newFilePath = $filePath . Datastore::MARK_FILE_AS_DELETED;
            $datastore->renameFile($filePath, $newFilePath);
            $file->setPhysicalFilePath($newFilePath);
        }

        $entityManager->persist($fileset);
        $entityManager->flush();

        return new Response(
            null,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }

    /**
     * Update a file entity.
     *
     * @param File                   $file          File entity instance.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     * @param Request                $request       The request body sent with destination directory.
     *
     * @Route("/api/file/{id}", name="pelagos_api_file_update_filename", methods={"PUT"}, defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_EDIT", subject="file")
     *
     * @throws BadRequestHttpException When the destination file name already exists.
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function updateFileName(File $file, EntityManagerInterface $entityManager, Request $request) : Response
    {
        $newFileName = $request->get('destinationDir');
        $fileset = $file->getFileset();
        if (!$fileset->doesFileExist($newFileName)) {
            $file->setFilePathName($newFileName);
            $entityManager->flush();
        } else {
            throw new BadRequestHttpException('File with same name and folder already exists');
        }

        return new Response(
            null,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }
}
