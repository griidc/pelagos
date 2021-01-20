<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD API for File Entity.
 */
class FileController extends AbstractFOSRestController
{
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
}
