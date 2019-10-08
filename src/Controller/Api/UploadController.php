<?php

namespace App\Controller\Api;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use App\Handler\EntityHandler;
use App\Handler\UploadHandler;

/**
 * The upload api controller.
 */
class UploadController extends EntityController
{
    /**
     * @var UploadHandler
     */
    protected $uploadHandler;

    /**
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * @var FormFactoryInterface Form factory instance.
     */
    protected $formFactory;

    /**
     * TreeController constructor.
     *
     * @param UploadHandler $uploadHandler
     * @param EntityHandler $entityHandler
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(UploadHandler $uploadHandler, EntityHandler $entityHandler, FormFactoryInterface $formFactory)
    {
        $this->entityHandler = $entityHandler;
        $this->formFactory = $formFactory;
        parent::__construct($entityHandler, $formFactory);
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Process a post of a file or a file chunk.
     *
     * @param Request $request The Symfony request object.
     *
     * @View()
     *
     * @Route(
     *     "/api/upload",
     *     name="pelagos_api_upload_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return array The result of the post.
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->uploadHandler->handleUpload($request);
    }

    /**
     * Delete an uploaded file.
     *
     * @param string $uuid The UUID of the file to delete.
     *
     * @View()
     *
     * @Route(
     *     "/api/upload/{uuid}",
     *     name="pelagos_api_upload_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return array The result of the delete.
     */
    public function deleteAction($uuid)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->uploadHandler->handleDelete($uuid);
    }
}
