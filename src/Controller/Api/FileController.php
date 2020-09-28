<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Repository\FileRepository;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
     * @param File                   $file           File entity instance.
     * @param MessageBusInterface    $messageBus     Symfony messenger bus interface instance.
     * @param EntityManagerInterface $entityManager  Entity manager interface instance.
     * 
     * @Route("/api/file/{id}", name="pelagos_api_datasets_delete", methods={"DELETE"}, defaults={"_format"="json"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("CAN_DELETE", subject="file")
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteFile(File $file, MessageBusInterface $messageBus, EntityManagerInterface $entityManager)
    {
        $fileset = $file->getFileset();
        $fileset->removeFile($file);
        $filePath = $file->getFilePath();
        $deleteFileMessage = new DeleteFile($filePath);
        $messageBus->dispatch($deleteFileMessage);
        $entityManager->persist($fileset);
        $entityManager->flush();

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }
}
