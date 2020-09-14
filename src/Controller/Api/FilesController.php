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
 * Files API Controller.
 */
class FilesController extends EntityController
{
    /**
     * Return a list of files uploaded for a dataset submission.
     *
     * @param integer $id The id of the dataset submission.
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_get_files_dataset_submission",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return array The list of uploaded files.
     */
    public function getFiles(int $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $fileData = array();
        $datasetSubmission = $this->handleGetOne(DatasetSubmission::class, $id);

        if ($datasetSubmission->getFileset() instanceof Fileset) {
            foreach ($datasetSubmission->getFileset()->getFiles() as $file) {
                $fileData[] = array(
                    'name' => $file->getFileName(),
                    'size' => $file-> getFileSize(),
                    'dateModified' => $file->getUploadedAt(),
                    'isDirectory' => false,
                    'hasSubDirectories' => false
                );
            }
        }

        return $fileData;
    }

    /**
     * Process a post of a file or a file chunk.
     *
     * @param Request                $request       The Symfony request object.
     * @param FileUploader           $fileUploader  File upload handler service.
     * @param EntityManagerInterface $entityManager Entity manager interface instance.
     * @param string                 $id            Dataset submission id.
     *
     * @View()
     *
     * @Route(
     *     "/api/files_dataset_submission/{id}",
     *     name="pelagos_api_post_files_dataset_submission",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response The result of the post.
     */
    public function postFiles(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager, string $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $fileUploader->upload($request);
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
     *     "/api/files_dataset_submission/combine-chunks/{id}",
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
                $entityManager->persist($fileset);
                $entityManager->flush();
            }
        }
        $entityManager->persist($datasetSubmission);
        $entityManager->flush();
    }
}
