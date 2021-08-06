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
    public function postChunks(Request $request, FileUploader $fileUploader)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse([
                'message' => 'NOT AUTHENTICATED'
            ], 401);
        }

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
     * @param Request      $request      The Symfony request object.
     * @param FileUploader $fileUploader File upload handler service.
     *
     * @View()
     *
     * @Route(
     *     "/api/files/combine-chunks/{id}",
     *     name="pelagos_api_combine_chunks",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response The result of the post.
     */
    public function combineChunks(Request $request, FileUploader $fileUploader)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse([
                'message' => 'NOT AUTHENTICATED'
            ], 401);
        }

        try {
            $fileMetadata = $fileUploader->combineChunks($request);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
        return $this->makeJsonResponse($fileMetadata);
    }
}
