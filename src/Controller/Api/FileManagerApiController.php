<?php

namespace App\Controller\Api;

use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * File Manager API to manipulate the file manager and uploader.
 */
class FileManagerApiController extends AbstractController
{
    /**
     * The Entity Manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Class constructor.
     *
     * @param EntityManagerInterface $em A Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * Get File Data as JSON.
     *
     * @Route("/api/files/{id}", name="pelagos_app_ui_files_get", methods={"GET"})
     *
     * @param int $id
     *
     * @return Response
     */
    public function getFilesAction(int $id): Response
    {
        $fileData = array();
        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($id);

        if ($datasetSubmission instanceof DatasetSubmission) {
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
        }

        $response = new Response(json_encode($fileData));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
