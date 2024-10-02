<?php

namespace App\Controller\Api;

use App\Util\FileUploader;
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
     * @param Request      $request      The Symfony request object.
     * @param FileUploader $fileUploader File upload handler service.
     *
     *
     * @Route(
     *     "/api/files/upload-chunks",
     *     name="pelagos_api_post_chunks",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     * @return Response The result of the post.
     */
    #[View]
    public function postChunks(Request $request, FileUploader $fileUploader)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $fileUploader->uploadChunk($request);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return $this->makeNoContentResponse();
    }
}
