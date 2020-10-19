<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;

use App\Util\FileUploader;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Upload file API Controller.
 */
class UploadController extends EntityController
{
    /**
     * Process a post of a file chunk.
     *
     * @param Request                $request       The Symfony request object.
     * @param FileUploader           $fileUploader  File upload handler service.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     *
     * @View()
     *
     * @Route(
     *     "/api/files/upload-chunks",
     *     name="pelagos_api_post_chunks",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response The result of the post.
     */
    public function postChunks(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $fileUploader->uploadChunk($request);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return $this->makeNoContentResponse();
    }

    /**
     * Combine file chunks.
     *
     * @param Request                $request       The Symfony request object.
     * @param FileUploader           $fileUploader  File upload handler service.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     * @param string                 $id            Dataset submission id.
     *
     * @View()
     *
     * @Route(
     *     "/api/files/combine-chunks/{id}",
     *     name="pelagos_api_combine_chunks",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response The result of the post.
     */
    public function combineChunks(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager, string $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $fileMetadata = $fileUploader->combineChunks($request);
            $this->updateFileEntity($fileMetadata, $entityManager, $id);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
        return $this->makeNoContentResponse();
    }

    /**
     * Update Fileset entity.
     *
     * @param array                  $fileMetadata  File metadata for the uploaded file.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     * @param string                 $id            Dataset submission id.
     *
     * @return void
     */
    private function updateFileEntity(array $fileMetadata, EntityManagerInterface $entityManager, string $id): void
    {
        $datasetSubmission = $entityManager->getRepository(DatasetSubmission::class)->find($id);
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileset = $datasetSubmission->getFileset();
            if ($fileset instanceof Fileset) {
                $newFile = new File();
                $newFile->setFileName($fileMetadata['name']);
                $newFile->setFileSize($fileMetadata['size']);
                $newFile->setUploadedAt(new \DateTime('now'));
                $newFile->setUploadedBy($this->getUser()->getPerson());
                $newFile->setFilePath($fileMetadata['path']);
                $fileset->addFile($newFile);
                $entityManager->persist($newFile);
            }
        }
        $entityManager->flush();
    }
}
