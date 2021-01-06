<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/api/file/{id}", name="pelagos_api_datasets_delete", methods={"DELETE"}, defaults={"_format"="json"})
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
            unlink($file->getPhysicalFilePath());
            $fileset->removeFile($file);
        } elseif ($file->getStatus() === File::FILE_DONE) {
            $newFilePath = $filePath . Datastore::MARK_FILE_AS_DELETED;
            $datastore->renameFile($filePath, $newFilePath);
            $file->setPhysicalFilePath($newFilePath);
        }

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
