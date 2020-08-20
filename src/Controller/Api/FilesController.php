<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\Fileset;

use App\Util\FileUploader;
use FOS\RestBundle\Controller\Annotations\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;

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
     * @param Request       $request      The Symfony request object.
     * @param FileUploader  $fileUploader File upload handler service.
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
    public function postFiles(Request $request, FileUploader $fileUploader)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        dump($request->files);
//        $file = new File();
//        $fileUploader->upload($file);

        return $this->makeNoContentResponse();
    }
}