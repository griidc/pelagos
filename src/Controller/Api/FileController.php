<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Repository\FileRepository;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;

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
     * Delete a Dataset and associated Metadata and Difs.
     *
     * @param string                 $id             The id of the File to delete.
     * @param FileRepository         $fileRepository File entity Repository instance.
     * @param MessageBusInterface    $messageBus     Symfony messenger bus interface instance.
     * @param EntityManagerInterface $entityManager  Entity manager interface instance.
     * @Route("/api/file/{id}", name="pelagos_api_datasets_delete", methods={"DELETE"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteFile(string $id, FileRepository $fileRepository, MessageBusInterface $messageBus, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $file = $fileRepository->find((int)$id);

        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            $deleteFileMessage = new DeleteFile($filePath);
            $messageBus->dispatch($deleteFileMessage);
            $entityManager->remove($file);
            $entityManager->flush();
        } else {
            throw new BadRequestHttpException('File does not exist');
        }

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }
}
